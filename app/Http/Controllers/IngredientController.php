<?php

namespace App\Http\Controllers;

use App\Contracts\RecipeRepository;
use App\Models\Ingredient;
use App\Services\RecipeService;
use App\Support\IngredientCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IngredientController extends Controller
{
    protected RecipeRepository $recipeService;

    protected RecipeService $catalogService;

    public function __construct(RecipeRepository $recipeService, RecipeService $catalogService)
    {
        $this->recipeService = $recipeService;
        $this->catalogService = $catalogService;
    }

    /**
     * Display the ingredient catalog browser for the user.
     */
    public function index()
    {
        $ingredients = Auth::user()->ingredients()->orderBy('name')->get();
        $catalog = $this->catalogService->getIngredientCatalog();
        $categories = IngredientCategories::all();
        $popular = IngredientCategories::popularFromCatalog($catalog);

        return view('ingredients.index', compact('ingredients', 'catalog', 'categories', 'popular'));
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
            'suggestions' => $suggestions,
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
                'suggestions' => [],
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
        if (! $exists) {
            $suggestions = $this->recipeService->getIngredientSuggestions($ingredient);
        }

        return response()->json([
            'exists' => $exists,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Accepts either a single legacy `name` field (also used for custom
     * ingredients outside the catalog) or a `names[]` bulk payload from the
     * multi-select catalog picker.
     */
    public function store(Request $request)
    {
        if ($request->filled('names')) {
            return $this->storeMany($request);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Auth::user()->ingredients()->create([
            'name' => trim($request->name),
        ]);

        return redirect()->route('ingredients.index')
            ->with('success', 'Ingredient added successfully.');
    }

    /**
     * Bulk-store catalog ingredients, skipping ones the user already has.
     */
    private function storeMany(Request $request)
    {
        $data = $request->validate([
            'names' => 'required|array|min:1|max:50',
            'names.*' => 'required|string|max:255',
        ]);

        $catalogNames = array_map('mb_strtolower', $this->catalogService->getAllIngredients());

        foreach ($data['names'] as $name) {
            if (! in_array(mb_strtolower(trim($name)), $catalogNames, true)) {
                return back()
                    ->withErrors(['names' => "\"{$name}\" is not in the TheMealDB ingredient catalog."])
                    ->withInput();
            }
        }

        $existing = Auth::user()->ingredients()
            ->get()
            ->map(fn (Ingredient $ingredient) => mb_strtolower($ingredient->name))
            ->all();

        $new = [];
        foreach ($data['names'] as $name) {
            $name = trim($name);
            $key = mb_strtolower($name);

            if (in_array($key, $existing, true) || in_array($key, array_map('mb_strtolower', $new), true)) {
                continue;
            }

            $new[] = $name;
        }

        $skipped = count($data['names']) - count($new);

        if ($new === []) {
            return redirect()->route('ingredients.index')
                ->with('success', 'Those ingredients are already in your list.');
        }

        foreach ($new as $name) {
            Auth::user()->ingredients()->create([
                'name' => $name,
            ]);
        }

        $message = count($new).(count($new) === 1 ? ' ingredient' : ' ingredients').' added successfully.';
        if ($skipped > 0) {
            $message .= " {$skipped} already in your list.";
        }

        return redirect()->route('ingredients.index')->with('success', $message);
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
