<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * Get user's favorite recipes
     */
    public function index(): JsonResponse
    {
        $favorites = Auth::user()->savedRecipes()
            ->where('is_favorite', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'favorites' => $favorites,
            'count' => $favorites->count(),
        ]);
    }
}
