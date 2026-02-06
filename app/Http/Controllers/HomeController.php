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

            return view('home', compact('ingredientsCount', 'savedRecipesCount', 'favoritesCount', 'recentRecipes', 'randomMeals'));
        }

        return view('home', compact('randomMeals'));
    }
}
