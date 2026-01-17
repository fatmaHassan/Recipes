<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RecipeService
{
    private string $baseUrl = 'https://www.themealdb.com/api/json/v1/1/';

    /**
     * Search recipes by ingredient
     */
    public function searchByIngredient(string $ingredient): array
    {
        $cacheKey = "recipe_search_{$ingredient}";
        
        return Cache::remember($cacheKey, 3600, function () use ($ingredient) {
            try {
                $response = Http::get($this->baseUrl . 'filter.php', [
                    'i' => $ingredient
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $meals = $data['meals'] ?? null;
                    
                    // TheMealDB returns null (not empty array) when no results found
                    if ($meals === null) {
                        return [];
                    }
                    
                    return $meals;
                }

                Log::warning('TheMealDB API request failed', [
                    'ingredient' => $ingredient,
                    'status' => $response->status()
                ]);

                return [];
            } catch (\Exception $e) {
                Log::error('Error fetching recipes from TheMealDB', [
                    'ingredient' => $ingredient,
                    'error' => $e->getMessage()
                ]);

                return [];
            }
        });
    }
    
    /**
     * Get suggested ingredient alternatives for generic terms
     */
    public function getIngredientSuggestions(string $ingredient): array
    {
        $suggestions = [
            'meat' => ['chicken', 'beef', 'pork', 'lamb', 'turkey'],
            'chicken' => ['chicken breast', 'chicken thigh', 'chicken wing'],
            'beef' => ['ground beef', 'beef steak', 'beef roast'],
            'fish' => ['salmon', 'tuna', 'cod', 'tilapia'],
            'vegetable' => ['carrot', 'onion', 'tomato', 'potato', 'broccoli'],
            'dairy' => ['milk', 'cheese', 'butter', 'cream'],
        ];
        
        $ingredientLower = strtolower($ingredient);
        
        if (isset($suggestions[$ingredientLower])) {
            return $suggestions[$ingredientLower];
        }
        
        return [];
    }

    /**
     * Search recipes by multiple ingredients
     */
    public function searchByIngredients(array $ingredients): array
    {
        $allRecipes = [];
        $recipeIds = [];

        foreach ($ingredients as $ingredient) {
            $recipes = $this->searchByIngredient($ingredient);
            
            foreach ($recipes as $recipe) {
                $id = $recipe['idMeal'] ?? null;
                if ($id && !in_array($id, $recipeIds)) {
                    $recipeIds[] = $id;
                    $allRecipes[] = $recipe;
                }
            }
        }

        return $allRecipes;
    }

    /**
     * Get full recipe details by ID
     */
    public function getRecipeDetails(string $recipeId): ?array
    {
        $cacheKey = "recipe_details_{$recipeId}";
        
        return Cache::remember($cacheKey, 3600, function () use ($recipeId) {
            try {
                $response = Http::get($this->baseUrl . 'lookup.php', [
                    'i' => $recipeId
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['meals'][0] ?? null;
                }

                return null;
            } catch (\Exception $e) {
                Log::error('Error fetching recipe details from TheMealDB', [
                    'recipe_id' => $recipeId,
                    'error' => $e->getMessage()
                ]);

                return null;
            }
        });
    }

    /**
     * Filter recipes by allergies
     */
    public function filterByAllergies(array $recipes, array $allergies): array
    {
        if (empty($allergies)) {
            return $recipes;
        }

        $filtered = [];
        
        foreach ($recipes as $recipe) {
            $recipeDetails = $this->getRecipeDetails($recipe['idMeal'] ?? '');
            
            if (!$recipeDetails) {
                continue;
            }

            $recipeIngredients = $this->extractIngredients($recipeDetails);
            $hasAllergen = false;

            foreach ($allergies as $allergy) {
                $allergenName = strtolower($allergy['allergen_name'] ?? '');
                
                foreach ($recipeIngredients as $ingredient) {
                    if (stripos(strtolower($ingredient), $allergenName) !== false) {
                        $hasAllergen = true;
                        break 2;
                    }
                }
            }

            if (!$hasAllergen) {
                $filtered[] = $recipe;
            }
        }

        return $filtered;
    }

    /**
     * Extract ingredients from recipe details
     */
    private function extractIngredients(array $recipeDetails): array
    {
        $ingredients = [];
        
        for ($i = 1; $i <= 20; $i++) {
            $ingredient = $recipeDetails["strIngredient{$i}"] ?? null;
            if ($ingredient && trim($ingredient) !== '') {
                $ingredients[] = trim($ingredient);
            }
        }

        return $ingredients;
    }
}
