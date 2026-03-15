<?php

namespace App\Http\Controllers;

use App\Services\RecipeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    protected RecipeService $recipeService;

    public function __construct(RecipeService $recipeService)
    {
        $this->recipeService = $recipeService;
    }

    public function index()
    {
        // Get random meals for both logged in users and guests
        $randomMeals = $this->recipeService->getRandomMeals(6);
        
        // Get featured cuisines with flags (limit to 8 popular ones for the home page)
        $allCuisines = $this->recipeService->getCuisinesWithFlags();
        $featuredCuisineNames = ['Italian', 'Mexican', 'Chinese', 'Indian', 'Japanese', 'French', 'Thai', 'American'];
        $featuredCuisines = array_filter($allCuisines, function ($cuisine) use ($featuredCuisineNames) {
            return in_array($cuisine['name'], $featuredCuisineNames);
        });
        $featuredCuisines = array_values($featuredCuisines);
        
        // Filter by allergies if user is logged in
        if (Auth::check()) {
            $user = Auth::user();
            $allergies = $user->allergies()->get()->toArray();
            $randomMeals = $this->recipeService->filterByAllergies($randomMeals, $allergies);
            
            $ingredientsCount = $user->ingredients()->count();
            $savedRecipesCount = $user->savedRecipes()->count();
            $favoritesCount = $user->savedRecipes()->where('is_favorite', true)->count();
            $recentRecipes = $user->savedRecipes()
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();

            return view('home', compact('ingredientsCount', 'savedRecipesCount', 'favoritesCount', 'recentRecipes', 'randomMeals', 'featuredCuisines'));
        }

        return view('home', compact('randomMeals', 'featuredCuisines'));
    }
}
