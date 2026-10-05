<?php

namespace App\Repositories;

use App\Contracts\RecipeRepository;
use App\Services\RecipeService;

class MealDbApiRecipeRepository implements RecipeRepository
{
    public function __construct(private RecipeService $service) {}

    public function searchByIngredient(string $ingredient): array
    {
        return $this->service->searchByIngredient($ingredient);
    }

    public function searchByIngredients(array $ingredients): array
    {
        return $this->service->searchByIngredients($ingredients);
    }

    public function getRecipeDetails(string $recipeId): ?array
    {
        return $this->service->getRecipeDetails($recipeId);
    }

    public function filterByAllergies(array $recipes, array $allergies): array
    {
        return $this->service->filterByAllergies($recipes, $allergies);
    }

    public function getRandomMeals(int $count = 6): array
    {
        return $this->service->getRandomMeals($count);
    }

    public function getAllIngredients(): array
    {
        return $this->service->getAllIngredients();
    }

    public function searchIngredients(string $query, int $limit = 10): array
    {
        return $this->service->searchIngredients($query, $limit);
    }

    public function getIngredientSuggestions(string $ingredient): array
    {
        return $this->service->getIngredientSuggestions($ingredient);
    }

    public function getAllCuisines(): array
    {
        return $this->service->getAllCuisines();
    }

    public function searchByCuisine(string $cuisine): array
    {
        return $this->service->searchByCuisine($cuisine);
    }

    public function getCuisinesWithFlags(): array
    {
        return $this->service->getCuisinesWithFlags();
    }
}
