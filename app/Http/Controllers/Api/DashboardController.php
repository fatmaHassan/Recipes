<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Get dashboard data
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        
        $stats = [
            'ingredients_count' => $user->ingredients()->count(),
            'allergies_count' => $user->allergies()->count(),
            'saved_recipes_count' => $user->savedRecipes()->count(),
            'favorites_count' => $user->savedRecipes()->where('is_favorite', true)->count(),
        ];

        return response()->json([
            'user' => $user,
            'stats' => $stats,
        ]);
    }
}
