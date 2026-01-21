<?php

namespace App\Http\Controllers;

use App\Models\SavedRecipe;
use App\Services\RecipeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

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
    public function search(Request $request)
    {
        // If POST request, redirect to GET with ingredients as query parameters
        if ($request->isMethod('post')) {
            $request->validate([
                'ingredients' => 'required|array|min:1',
                'ingredients.*' => 'required|string',
            ]);

            return redirect()->route('recipes.search', [
                'ingredients' => $request->ingredients
            ]);
        }

        // Handle GET request (for pagination)
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

        // Paginate the recipes array
        $perPage = 12; // Number of recipes per page
        $currentPage = Paginator::resolveCurrentPage();
        $currentItems = array_slice($recipes, ($currentPage - 1) * $perPage, $perPage);
        
        $paginatedRecipes = new LengthAwarePaginator(
            $currentItems,
            count($recipes),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(), // Preserve query parameters (ingredients)
            ]
        );

        return view('recipes.index', compact('paginatedRecipes', 'ingredients', 'suggestions'));
    }

    /**
     * Show recipe details
     */
    public function show(string $id)
    {
        $recipe = $this->recipeService->getRecipeDetails($id);
        
        if (!$recipe) {
            abort(404, 'Recipe not found');
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

        return view('recipes.show', compact('recipe', 'isSaved', 'isFavorite'));
    }

    /**
     * Save a recipe
     */
    public function save(Request $request)
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
            return redirect()->back()
                ->with('info', 'Recipe is already saved.');
        }

        try {
            $isFavorite = $request->has('favorite') && $request->favorite == '1';
            
            $user->savedRecipes()->create([
                'recipe_id' => $request->recipe_id,
                'recipe_data' => $request->recipe_data,
                'is_favorite' => $isFavorite,
            ]);

            $message = $isFavorite 
                ? 'Recipe saved and added to favorites!'
                : 'Recipe saved successfully! You can find it in "My Recipes".';

            return redirect()->back()
                ->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Error saving recipe: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to save recipe. Please try again.');
        }
    }

    /**
     * Toggle favorite status
     */
    public function toggleFavorite(Request $request, string $recipeId)
    {
        $savedRecipe = Auth::user()->savedRecipes()
            ->where('recipe_id', $recipeId)
            ->first();

        if (!$savedRecipe) {
            return redirect()->back()
                ->with('error', 'Recipe not found in saved recipes.');
        }

        $savedRecipe->update([
            'is_favorite' => !$savedRecipe->is_favorite,
        ]);

        $message = $savedRecipe->is_favorite 
            ? 'Recipe added to favorites.' 
            : 'Recipe removed from favorites.';

        return redirect()->back()
            ->with('success', $message);
    }
}
