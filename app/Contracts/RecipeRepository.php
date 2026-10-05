<?php

namespace App\Contracts;

interface RecipeRepository
{
    public function searchByIngredient(string $ingredient): array;

    public function searchByIngredients(array $ingredients): array;

    public function getRecipeDetails(string $recipeId): ?array;

    public function filterByAllergies(array $recipes, array $allergies): array;

    public function getRandomMeals(int $count = 6): array;

    public function getAllIngredients(): array;

    public function searchIngredients(string $query, int $limit = 10): array;

    public function getIngredientSuggestions(string $ingredient): array;

    public function getAllCuisines(): array;

    public function searchByCuisine(string $cuisine): array;

    public function getCuisinesWithFlags(): array;
}
