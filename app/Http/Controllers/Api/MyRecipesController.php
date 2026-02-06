<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MyRecipesController extends Controller
{
    /**
     * Get user's saved recipes
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $savedRecipes = $user->savedRecipes()
                ->orderBy('created_at', 'desc')
                ->get();

            // Transform the saved recipes to include recipe_data
            $recipes = $savedRecipes->map(function ($savedRecipe) {
                $recipeData = $savedRecipe->recipe_data ?? [];
                return array_merge([
                    'id' => $savedRecipe->recipe_id,
                    'saved_id' => $savedRecipe->id,
                    'is_favorite' => $savedRecipe->is_favorite,
                    'saved_at' => $savedRecipe->created_at,
                ], $recipeData);
            })->values()->toArray(); // Convert Collection to array

            return response()->json([
                'recipes' => $recipes,
                'data' => $recipes, // Also include as 'data' for compatibility
                'count' => count($recipes),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching my recipes: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch recipes',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
