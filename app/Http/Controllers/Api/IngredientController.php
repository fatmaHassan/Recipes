<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Services\RecipeService;
use Illuminate\Http\JsonResponse;
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
     * Get user's ingredients
     */
    public function index(): JsonResponse
    {
        $ingredients = Auth::user()->ingredients()->orderBy('name')->get();

        return response()->json([
            'ingredients' => $ingredients,
        ]);
    }

    /**
     * Store a new ingredient
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $ingredient = Auth::user()->ingredients()->create([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'Ingredient added successfully',
            'ingredient' => $ingredient,
        ], 201);
    }

    /**
     * Delete an ingredient
     */
    public function destroy(Ingredient $ingredient): JsonResponse
    {
        if ($ingredient->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $ingredient->delete();

        return response()->json([
            'message' => 'Ingredient deleted successfully',
        ]);
    }

    /**
     * Search ingredients for autocomplete
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $limit = min((int) $request->input('limit', 10), 20);

        $suggestions = $this->recipeService->searchIngredients($query, $limit);

        return response()->json([
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Check if ingredient exists and get suggestions if not found
     */
    public function check(Request $request): JsonResponse
    {
        $ingredient = $request->input('ingredient', '');

        if (empty($ingredient)) {
            return response()->json([
                'exists' => false,
                'suggestions' => [],
            ]);
        }

        $allIngredients = $this->recipeService->getAllIngredients();
        $ingredientLower = strtolower(trim($ingredient));

        $exists = false;
        foreach ($allIngredients as $dbIngredient) {
            if (strtolower($dbIngredient) === $ingredientLower) {
                $exists = true;
                break;
            }
        }

        $suggestions = [];
        if (!$exists) {
            $suggestions = $this->recipeService->getIngredientSuggestions($ingredient);
        }

        return response()->json([
            'exists' => $exists,
            'suggestions' => $suggestions,
        ]);
    }
}
