<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SavedRecipe;
use App\Services\RecipeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecipeController extends Controller
{
    protected RecipeService $recipeService;

    public function __construct(RecipeService $recipeService)
    {
        $this->recipeService = $recipeService;
    }

    /**
     * Search recipes by selected ingredients
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'ingredients' => 'required|array|min:1',
            'ingredients.*' => 'required|string',
        ]);

        $ingredients = $request->ingredients;
        $recipes = $this->recipeService->searchByIngredients($ingredients);

        // Collect suggestions for ingredients that returned no results
        $suggestions = [];
        foreach ($ingredients as $ingredient) {
            $ingredientResults = $this->recipeService->searchByIngredient($ingredient);
            if (empty($ingredientResults)) {
                $altSuggestions = $this->recipeService->getIngredientSuggestions($ingredient);
                if (!empty($altSuggestions)) {
                    $suggestions[$ingredient] = $altSuggestions;
                }
            }
        }

        // Filter by allergies if user is logged in
        if (Auth::check()) {
            $allergies = Auth::user()->allergies()->get()->toArray();
            $recipes = $this->recipeService->filterByAllergies($recipes, $allergies);
        }

        return response()->json([
            'recipes' => $recipes,
            'suggestions' => $suggestions,
            'count' => count($recipes),
        ]);
    }

    /**
     * Show recipe details
     */
    public function show(string $id): JsonResponse
    {
        $recipe = $this->recipeService->getRecipeDetails($id);

        if (!$recipe) {
            return response()->json([
                'message' => 'Recipe not found',
            ], 404);
        }

        $isSaved = false;
        $isFavorite = false;

        if (Auth::check()) {
            $savedRecipe = Auth::user()->savedRecipes()
                ->where('recipe_id', $id)
                ->first();

            if ($savedRecipe) {
                $isSaved = true;
                $isFavorite = $savedRecipe->is_favorite;
            }
        }

        return response()->json([
            'recipe' => $recipe,
            'is_saved' => $isSaved,
            'is_favorite' => $isFavorite,
        ]);
    }

    /**
     * Save a recipe
     */
    public function save(Request $request): JsonResponse
    {
        $request->validate([
            'recipe_id' => 'required|string',
            'recipe_data' => 'required|array',
        ]);

        $user = Auth::user();

        // Check if already saved
        $existing = $user->savedRecipes()
            ->where('recipe_id', $request->recipe_id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Recipe is already saved',
                'recipe' => $existing,
            ], 409);
        }

        try {
            $isFavorite = $request->has('favorite') && $request->favorite == '1';

            $savedRecipe = $user->savedRecipes()->create([
                'recipe_id' => $request->recipe_id,
                'recipe_data' => $request->recipe_data,
                'is_favorite' => $isFavorite,
            ]);

            $message = $isFavorite
                ? 'Recipe saved and added to favorites!'
                : 'Recipe saved successfully!';

            return response()->json([
                'message' => $message,
                'recipe' => $savedRecipe,
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error saving recipe: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to save recipe. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle favorite status
     */
    public function toggleFavorite(Request $request, string $recipeId): JsonResponse
    {
        $savedRecipe = Auth::user()->savedRecipes()
            ->where('recipe_id', $recipeId)
            ->first();

        if (!$savedRecipe) {
            return response()->json([
                'message' => 'Recipe not found in saved recipes',
            ], 404);
        }

        $savedRecipe->update([
            'is_favorite' => !$savedRecipe->is_favorite,
        ]);

        $message = $savedRecipe->is_favorite
            ? 'Recipe added to favorites.'
            : 'Recipe removed from favorites.';

        return response()->json([
            'message' => $message,
            'recipe' => $savedRecipe,
        ]);
    }
}
