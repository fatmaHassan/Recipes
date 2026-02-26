<?php

namespace App\Http\Controllers;

use App\Services\RecipeService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;

class CuisineController extends Controller
{
    protected RecipeService $recipeService;

    public function __construct(RecipeService $recipeService)
    {
        $this->recipeService = $recipeService;
    }

    /**
     * Display all available cuisines
     */
    public function index()
    {
        $cuisines = $this->recipeService->getCuisinesWithFlags();
        
        return view('cuisines.index', compact('cuisines'));
    }

    /**
     * Display recipes for a specific cuisine
     */
    public function show(Request $request, string $cuisine)
    {
        $recipes = $this->recipeService->searchByCuisine($cuisine);
        
        // Filter by allergies if user is logged in
        if (Auth::check()) {
            $allergies = Auth::user()->allergies()->get()->toArray();
            $recipes = $this->recipeService->filterByAllergies($recipes, $allergies);
        }

        // Paginate the recipes array
        $perPage = 12;
        $currentPage = Paginator::resolveCurrentPage();
        $currentItems = array_slice($recipes, ($currentPage - 1) * $perPage, $perPage);
        
        $paginatedRecipes = new LengthAwarePaginator(
            $currentItems,
            count($recipes),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        // Get cuisine info with flag
        $cuisines = $this->recipeService->getCuisinesWithFlags();
        $cuisineInfo = collect($cuisines)->firstWhere('name', $cuisine);

        return view('cuisines.show', compact('paginatedRecipes', 'cuisine', 'cuisineInfo'));
    }
}
