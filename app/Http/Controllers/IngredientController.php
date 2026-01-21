<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Services\RecipeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IngredientController extends Controller
{
    protected RecipeService $recipeService;

    public function __construct(RecipeService $recipeService)
    {
        $this->recipeService = $recipeService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ingredients = Auth::user()->ingredients()->orderBy('name')->get();
        return view('ingredients.index', compact('ingredients'));
    }

    /**
     * Search ingredients for autocomplete
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $limit = min((int) $request->input('limit', 10), 20); // Max 20 results
        
        $suggestions = $this->recipeService->searchIngredients($query, $limit);
        
        return response()->json([
            'suggestions' => $suggestions
        ]);
    }

    /**
     * Check if ingredient exists and get suggestions if not found
     */
    public function check(Request $request)
    {
        $ingredient = $request->input('ingredient', '');
        
        if (empty($ingredient)) {
            return response()->json([
                'exists' => false,
                'suggestions' => []
            ]);
        }
        
        $allIngredients = $this->recipeService->getAllIngredients();
        $ingredientLower = strtolower(trim($ingredient));
        
        // Check if exact match exists
        $exists = false;
        foreach ($allIngredients as $dbIngredient) {
            if (strtolower($dbIngredient) === $ingredientLower) {
                $exists = true;
                break;
            }
        }
        
        // Get suggestions if not found
        $suggestions = [];
        if (!$exists) {
            $suggestions = $this->recipeService->getIngredientSuggestions($ingredient);
        }
        
        return response()->json([
            'exists' => $exists,
            'suggestions' => $suggestions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Auth::user()->ingredients()->create([
            'name' => $request->name,
        ]);

        return redirect()->route('ingredients.index')
            ->with('success', 'Ingredient added successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ingredient $ingredient)
    {
        if ($ingredient->user_id !== Auth::id()) {
            abort(403);
        }

        $ingredient->delete();

        return redirect()->route('ingredients.index')
            ->with('success', 'Ingredient deleted successfully.');
    }
}
