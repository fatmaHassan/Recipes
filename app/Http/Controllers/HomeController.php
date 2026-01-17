<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $ingredientsCount = $user->ingredients()->count();
            $savedRecipesCount = $user->savedRecipes()->count();
            $favoritesCount = $user->savedRecipes()->where('is_favorite', true)->count();
            $recentRecipes = $user->savedRecipes()
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();

            return view('home', compact('ingredientsCount', 'savedRecipesCount', 'favoritesCount', 'recentRecipes'));
        }

        return view('home');
    }
}
