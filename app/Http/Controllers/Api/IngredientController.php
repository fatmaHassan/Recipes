<?php

namespace App\Http\Controllers\Api;

use App\Contracts\RecipeRepository;
use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IngredientController extends Controller
{
    protected RecipeRepository $recipeService;

    public function __construct(RecipeRepository $recipeService)
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
     * Store a new ingredient.
     *
     * Accepts a single `name` or a `names[]` bulk payload validated against
     * the TheMealDB ingredient catalog.
     */
    public function store(Request $request): JsonResponse
    {
        if ($request->filled('names')) {
            return $this->storeMany($request);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $ingredient = Auth::user()->ingredients()->create([
            'name' => trim($request->name),
        ]);

        return response()->json([
            'message' => 'Ingredient added successfully',
            'ingredient' => $ingredient,
        ], 201);
    }

    /**
     * Bulk-store catalog ingredients, skipping ones the user already has.
     */
    private function storeMany(Request $request): JsonResponse
    {
        $data = $request->validate([
            'names' => 'required|array|min:1|max:50',
            'names.*' => 'required|string|max:255',
        ]);

        $catalogNames = array_map('mb_strtolower', $this->recipeService->getAllIngredients());

        foreach ($data['names'] as $name) {
            if (! in_array(mb_strtolower(trim($name)), $catalogNames, true)) {
                return response()->json([
                    'message' => "\"{$name}\" is not in the TheMealDB ingredient catalog.",
                ], 422);
            }
        }

        $existing = Auth::user()->ingredients()
            ->get()
            ->map(fn (Ingredient $ingredient) => mb_strtolower($ingredient->name))
            ->all();

        $created = [];
        foreach ($data['names'] as $name) {
            $name = trim($name);
            $key = mb_strtolower($name);

            if (in_array($key, $existing, true) || in_array($key, array_map('mb_strtolower', array_column($created, 'name')), true)) {
                continue;
            }

            $created[] = Auth::user()->ingredients()->create([
                'name' => $name,
            ])->only(['id', 'name']);
        }

        return response()->json([
            'message' => count($created).' ingredient'.(count($created) === 1 ? '' : 's').' added successfully',
            'created' => $created,
            'skipped' => count($data['names']) - count($created),
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
        if (! $exists) {
            $suggestions = $this->recipeService->getIngredientSuggestions($ingredient);
        }

        return response()->json([
            'exists' => $exists,
            'suggestions' => $suggestions,
        ]);
    }
}
