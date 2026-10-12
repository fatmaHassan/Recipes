<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class MyRecipesController extends Controller
{
    public function index()
    {
        $savedRecipes = Auth::user()->savedRecipes()
            ->with('recipe')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('my-recipes.index', compact('savedRecipes'));
    }
}
