<?php

namespace App\Repositories;

use App\Contracts\RecipeRepository;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Support\CuisineFlags;
use App\Support\IngredientMatcher;
use App\Support\MealDbRecipePresenter;

class DatabaseRecipeRepository implements RecipeRepository
{
    public function searchByIngredient(string $ingredient): array
    {
        $normalized = mb_strtolower(trim($ingredient));

        if ($normalized === '') {
            return [];
        }

        return Recipe::query()
            ->where('is_active', true)
            ->whereHas('ingredients', fn ($query) => $query->where('name_normalized', $normalized))
            ->orderBy('title')
            ->get()
            ->map(fn (Recipe $recipe) => MealDbRecipePresenter::summary($recipe))
            ->all();
    }

    public function searchByIngredients(array $ingredients): array
    {
        $recipes = [];

        foreach ($ingredients as $ingredient) {
            foreach ($this->searchByIngredient($ingredient) as $recipe) {
                $recipes[$recipe['idMeal']] ??= $recipe;
            }
        }

        return array_values($recipes);
    }

    public function getRecipeDetails(string $recipeId): ?array
    {
        $recipe = Recipe::query()
            ->where('is_active', true)
            ->where('external_id', $recipeId)
            ->with('ingredients')
            ->first();

        return $recipe ? MealDbRecipePresenter::details($recipe) : null;
    }

    public function filterByAllergies(array $recipes, array $allergies): array
    {
        if (empty($allergies) || empty($recipes)) {
            return $recipes;
        }

        $allergens = array_values(array_filter(array_map(
            fn ($allergy) => strtolower($allergy['allergen_name'] ?? ''),
            $allergies
        )));

        if (empty($allergens)) {
            return $recipes;
        }

        $idMeals = array_column($recipes, 'idMeal');

        $safe = Recipe::query()
            ->where('is_active', true)
            ->whereIn('external_id', $idMeals)
            ->whereDoesntHave('ingredients', function ($query) use ($allergens) {
                $query->where(function ($query) use ($allergens) {
                    foreach ($allergens as $allergen) {
                        $query->orWhereRaw('lower(name) LIKE ?', ['%'.$allergen.'%']);
                    }
                });
            })
            ->pluck('external_id')
            ->flip();

        return array_values(array_filter(
            $recipes,
            fn ($recipe) => $safe->has($recipe['idMeal'] ?? null)
        ));
    }

    public function getRandomMeals(int $count = 6): array
    {
        return Recipe::query()
            ->where('is_active', true)
            ->inRandomOrder()
            ->limit(max(1, $count))
            ->with('ingredients')
            ->get()
            ->map(fn (Recipe $recipe) => MealDbRecipePresenter::details($recipe))
            ->all();
    }

    public function getAllIngredients(): array
    {
        return RecipeIngredient::query()
            ->join('recipes', 'recipes.id', '=', 'recipe_ingredients.recipe_id')
            ->where('recipes.is_active', true)
            ->distinct()
            ->orderBy('recipe_ingredients.name')
            ->pluck('recipe_ingredients.name')
            ->all();
    }

    public function searchIngredients(string $query, int $limit = 10): array
    {
        return IngredientMatcher::search($this->getAllIngredients(), $query, $limit);
    }

    public function getIngredientSuggestions(string $ingredient): array
    {
        return IngredientMatcher::suggestions($this->getAllIngredients(), $ingredient);
    }

    public function getAllCuisines(): array
    {
        return Recipe::query()
            ->where('is_active', true)
            ->whereNotNull('area')
            ->where('area', '!=', '')
            ->distinct()
            ->orderBy('area')
            ->pluck('area')
            ->all();
    }

    public function searchByCuisine(string $cuisine): array
    {
        return Recipe::query()
            ->where('is_active', true)
            ->where('area', $cuisine)
            ->orderBy('title')
            ->get()
            ->map(fn (Recipe $recipe) => MealDbRecipePresenter::summary($recipe))
            ->all();
    }

    public function getCuisinesWithFlags(): array
    {
        return CuisineFlags::forNames($this->getAllCuisines());
    }
}
