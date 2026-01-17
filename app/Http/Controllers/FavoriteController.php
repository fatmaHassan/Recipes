<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function index()
    {
        $favorites = Auth::user()->savedRecipes()
            ->where('is_favorite', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('favorites.index', compact('favorites'));
    }
}
