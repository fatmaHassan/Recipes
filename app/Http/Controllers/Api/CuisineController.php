<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RecipeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CuisineController extends Controller
{
    protected RecipeService $recipeService;

    public function __construct(RecipeService $recipeService)
    {
        $this->recipeService = $recipeService;
    }

    /**
     * Get all available cuisines with flags
     */
    public function index(): JsonResponse
    {
        $cuisines = $this->recipeService->getCuisinesWithFlags();
        
        return response()->json([
            'cuisines' => $cuisines,
            'count' => count($cuisines),
        ]);
    }

    /**
     * Get recipes for a specific cuisine
     */
    public function show(Request $request, string $cuisine): JsonResponse
    {
        $recipes = $this->recipeService->searchByCuisine($cuisine);
        
        if (empty($recipes)) {
            return response()->json([
                'message' => 'No recipes found for this cuisine',
                'cuisine' => $cuisine,
                'recipes' => [],
                'count' => 0,
            ]);
        }

        // Filter by allergies if user is logged in
        if (Auth::check()) {
            $allergies = Auth::user()->allergies()->get()->toArray();
            $recipes = $this->recipeService->filterByAllergies($recipes, $allergies);
        }

        return response()->json([
            'cuisine' => $cuisine,
            'recipes' => $recipes,
            'count' => count($recipes),
        ]);
    }
}
