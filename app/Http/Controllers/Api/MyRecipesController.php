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
        $savedRecipes = Auth::user()->savedRecipes()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'recipes' => $savedRecipes,
            'count' => $savedRecipes->count(),
        ]);
    }
}
