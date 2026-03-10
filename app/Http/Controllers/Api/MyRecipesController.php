<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyRecipesController extends Controller
{
    /**
     * Get user's saved recipes with pagination support
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated',
                ], 401);
            }

            // Get pagination parameters (default: 15 per page)
            $perPage = (int) $request->query('per_page', 15);
            $perPage = max(1, min($perPage, 50)); // Limit between 1 and 50
            
            // Paginate the saved recipes
            $paginatedRecipes = $user->savedRecipes()
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Transform the saved recipes to include recipe_data
            $recipes = $paginatedRecipes->getCollection()->map(function ($savedRecipe) {
                $recipeData = $savedRecipe->recipe_data ?? [];
                return array_merge([
                    'id' => $savedRecipe->recipe_id,
                    'saved_id' => $savedRecipe->id,
                    'is_favorite' => $savedRecipe->is_favorite,
                    'saved_at' => $savedRecipe->created_at,
                ], $recipeData);
            })->values()->toArray();

            return response()->json([
                'recipes' => $recipes,
                'data' => $recipes,
                'count' => count($recipes),
                'current_page' => $paginatedRecipes->currentPage(),
                'last_page' => $paginatedRecipes->lastPage(),
                'per_page' => $paginatedRecipes->perPage(),
                'total' => $paginatedRecipes->total(),
                'has_more_pages' => $paginatedRecipes->hasMorePages(),
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
