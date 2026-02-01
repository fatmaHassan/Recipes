<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Allergy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AllergyController extends Controller
{
    /**
     * Get user's allergies
     */
    public function index(): JsonResponse
    {
        $allergies = Auth::user()->allergies()->orderBy('allergen_name')->get();

        return response()->json([
            'allergies' => $allergies,
        ]);
    }

    /**
     * Store a new allergy
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'allergen_name' => 'required|string|max:255',
        ]);

        $allergy = Auth::user()->allergies()->create([
            'allergen_name' => $request->allergen_name,
        ]);

        return response()->json([
            'message' => 'Allergy added successfully',
            'allergy' => $allergy,
        ], 201);
    }

    /**
     * Delete an allergy
     */
    public function destroy(Allergy $allergy): JsonResponse
    {
        if ($allergy->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $allergy->delete();

        return response()->json([
            'message' => 'Allergy deleted successfully',
        ]);
    }
}
