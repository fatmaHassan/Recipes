<?php

namespace App\Http\Controllers;

use App\Models\CustomRecipe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomRecipeController extends Controller
{
    public function index(): View
    {
        $recipes = Auth::user()
            ->customRecipes()
            ->orderByDesc('updated_at')
            ->get();

        return view('custom-recipes.index', compact('recipes'));
    }

    public function create(): View
    {
        return view('custom-recipes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validatedRecipePayload($request);

        $recipe = DB::transaction(function () use ($payload) {
            $recipe = Auth::user()->customRecipes()->create([
                'title' => $payload['validated']['title'],
                'instructions' => $payload['validated']['instructions'],
                'category' => $payload['validated']['category'] ?? null,
                'area' => $payload['validated']['area'] ?? null,
                'image_url' => $payload['validated']['image_url'] ?? null,
                'servings' => $payload['validated']['servings'] ?? null,
                'prep_time_minutes' => $payload['validated']['prep_time_minutes'] ?? null,
                'cook_time_minutes' => $payload['validated']['cook_time_minutes'] ?? null,
            ]);

            $this->syncIngredients($recipe, $payload['ingredients']);

            return $recipe;
        });

        return redirect()
            ->route('custom-recipes.show', $recipe)
            ->with('success', __('Your custom recipe was saved.'));
    }

    public function show(CustomRecipe $custom_recipe): View
    {
        $this->ensureOwner($custom_recipe);
        $custom_recipe->load('ingredients');

        return view('custom-recipes.show', ['recipe' => $custom_recipe]);
    }

    public function edit(CustomRecipe $custom_recipe): View
    {
        $this->ensureOwner($custom_recipe);
        $custom_recipe->load('ingredients');

        return view('custom-recipes.edit', ['recipe' => $custom_recipe]);
    }

    public function update(Request $request, CustomRecipe $custom_recipe): RedirectResponse
    {
        $this->ensureOwner($custom_recipe);
        $payload = $this->validatedRecipePayload($request);

        DB::transaction(function () use ($custom_recipe, $payload) {
            $custom_recipe->update([
                'title' => $payload['validated']['title'],
                'instructions' => $payload['validated']['instructions'],
                'category' => $payload['validated']['category'] ?? null,
                'area' => $payload['validated']['area'] ?? null,
                'image_url' => $payload['validated']['image_url'] ?? null,
                'servings' => $payload['validated']['servings'] ?? null,
                'prep_time_minutes' => $payload['validated']['prep_time_minutes'] ?? null,
                'cook_time_minutes' => $payload['validated']['cook_time_minutes'] ?? null,
            ]);

            $custom_recipe->ingredients()->delete();
            $this->syncIngredients($custom_recipe, $payload['ingredients']);
        });

        return redirect()
            ->route('custom-recipes.show', $custom_recipe)
            ->with('success', __('Your custom recipe was updated.'));
    }

    private function ensureOwner(CustomRecipe $recipe): void
    {
        abort_unless($recipe->user_id === Auth::id(), 403);
    }

    /**
     * @return array{validated: array<string, mixed>, ingredients: Collection<int, array{name: string, measure: string}>}
     */
    private function validatedRecipePayload(Request $request): array
    {
        $this->normalizeRecipeRequest($request);

        $ingredientRows = $this->collectIngredientRows($request);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['required', 'string', 'max:65535'],
            'category' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'string', 'max:2048', 'url'],
            'servings' => ['nullable', 'string', 'max:50'],
            'prep_time_minutes' => ['nullable', 'integer', 'min:0', 'max:10080'],
            'cook_time_minutes' => ['nullable', 'integer', 'min:0', 'max:10080'],
        ]);

        if ($ingredientRows->isEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'ingredients' => __('Add at least one ingredient with a name.'),
            ]);
        }

        foreach ($ingredientRows as $index => $row) {
            if (strlen($row['name']) > 255) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "ingredients.{$index}.name" => __('Each ingredient name may be at most 255 characters.'),
                ]);
            }
            if ($row['measure'] !== '' && strlen($row['measure']) > 255) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "ingredients.{$index}.measure" => __('Each amount may be at most 255 characters.'),
                ]);
            }
        }

        return [
            'validated' => $validated,
            'ingredients' => $ingredientRows,
        ];
    }

    private function normalizeRecipeRequest(Request $request): void
    {
        if ($request->input('image_url') === '') {
            $request->merge(['image_url' => null]);
        }
        foreach (['prep_time_minutes', 'cook_time_minutes'] as $field) {
            if ($request->input($field) === '' || $request->input($field) === null) {
                $request->merge([$field => null]);
            }
        }
    }

    /**
     * @return Collection<int, array{name: string, measure: string}>
     */
    private function collectIngredientRows(Request $request): Collection
    {
        return collect($request->input('ingredients', []))
            ->map(fn ($row) => [
                'name' => isset($row['name']) ? trim((string) $row['name']) : '',
                'measure' => isset($row['measure']) ? trim((string) $row['measure']) : '',
            ])
            ->filter(fn ($row) => $row['name'] !== '')
            ->values();
    }

    /**
     * @param  Collection<int, array{name: string, measure: string}>  $ingredientRows
     */
    private function syncIngredients(CustomRecipe $recipe, Collection $ingredientRows): void
    {
        $sort = 0;
        foreach ($ingredientRows as $row) {
            $recipe->ingredients()->create([
                'sort_order' => $sort++,
                'name' => $row['name'],
                'measure' => $row['measure'] !== '' ? $row['measure'] : null,
            ]);
        }
    }
}
