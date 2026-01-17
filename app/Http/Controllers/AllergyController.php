<?php

namespace App\Http\Controllers;

use App\Models\Allergy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AllergyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allergies = Auth::user()->allergies()->orderBy('allergen_name')->get();
        return view('allergies.index', compact('allergies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'allergen_name' => 'required|string|max:255',
        ]);

        Auth::user()->allergies()->create([
            'allergen_name' => $request->allergen_name,
        ]);

        return redirect()->route('allergies.index')
            ->with('success', 'Allergy added successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Allergy $allergy)
    {
        if ($allergy->user_id !== Auth::id()) {
            abort(403);
        }

        $allergy->delete();

        return redirect()->route('allergies.index')
            ->with('success', 'Allergy deleted successfully.');
    }
}
