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
     * Get all ingredients from TheMealDB
     */
    public function getAllIngredients(): array
    {
        $cacheKey = 'themealdb_all_ingredients';
        
        return Cache::remember($cacheKey, 86400, function () { // Cache for 24 hours
            try {
                $response = Http::get($this->baseUrl . 'list.php', [
                    'i' => 'list'
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $meals = $data['meals'] ?? [];
                    
                    // Extract ingredient names
                    $ingredients = array_map(function ($item) {
                        return $item['strIngredient'] ?? '';
                    }, $meals);
                    
                    return array_filter($ingredients); // Remove empty values
                }

                Log::warning('TheMealDB API request failed for ingredients list', [
                    'status' => $response->status()
                ]);

                return [];
            } catch (\Exception $e) {
                Log::error('Error fetching ingredients list from TheMealDB', [
                    'error' => $e->getMessage()
                ]);

                return [];
            }
        });
    }

    /**
     * Search ingredients by query (for autocomplete)
     */
    public function searchIngredients(string $query, int $limit = 10): array
    {
        $allIngredients = $this->getAllIngredients();
        $queryLower = strtolower(trim($query));
        
        if (empty($queryLower)) {
            return array_slice($allIngredients, 0, $limit);
        }
        
        $matches = [];
        $exactMatches = [];
        $startsWithMatches = [];
        $containsMatches = [];
        
        foreach ($allIngredients as $ingredient) {
            $ingredientLower = strtolower($ingredient);
            
            if ($ingredientLower === $queryLower) {
                $exactMatches[] = $ingredient;
            } elseif (strpos($ingredientLower, $queryLower) === 0) {
                $startsWithMatches[] = $ingredient;
            } elseif (strpos($ingredientLower, $queryLower) !== false) {
                $containsMatches[] = $ingredient;
            }
        }
        
        // Combine matches in order of relevance
        $matches = array_merge($exactMatches, $startsWithMatches, $containsMatches);
        
        return array_slice($matches, 0, $limit);
    }

    /**
     * Get suggested ingredient alternatives for generic terms or similar ingredients
     */
    public function getIngredientSuggestions(string $ingredient): array
    {
        // First try to get suggestions from TheMealDB ingredients
        $allIngredients = $this->getAllIngredients();
        $ingredientLower = strtolower(trim($ingredient));
        
        // Find similar ingredients using fuzzy matching
        $suggestions = [];
        foreach ($allIngredients as $dbIngredient) {
            $dbIngredientLower = strtolower($dbIngredient);
            
            // Check if it's a close match (contains the search term or vice versa)
            if ($dbIngredientLower !== $ingredientLower) {
                if (strpos($dbIngredientLower, $ingredientLower) !== false || 
                    strpos($ingredientLower, $dbIngredientLower) !== false ||
                    similar_text($ingredientLower, $dbIngredientLower) / max(strlen($ingredientLower), strlen($dbIngredientLower)) > 0.6) {
                    $suggestions[] = $dbIngredient;
                }
            }
        }
        
        // Limit to top 5 suggestions
        $suggestions = array_slice($suggestions, 0, 5);
        
        // Fallback to hardcoded suggestions if no matches found
        if (empty($suggestions)) {
            $hardcodedSuggestions = [
                'meat' => ['chicken', 'beef', 'pork', 'lamb', 'turkey'],
                'chicken' => ['chicken breast', 'chicken thigh', 'chicken wing'],
                'beef' => ['ground beef', 'beef steak', 'beef roast'],
                'fish' => ['salmon', 'tuna', 'cod', 'tilapia'],
                'vegetable' => ['carrot', 'onion', 'tomato', 'potato', 'broccoli'],
                'dairy' => ['milk', 'cheese', 'butter', 'cream'],
            ];
            
            if (isset($hardcodedSuggestions[$ingredientLower])) {
                return $hardcodedSuggestions[$ingredientLower];
            }
        }
        
        return $suggestions;
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
     * Get random meals from TheMealDB
     * 
     * @param int $count Number of random meals to fetch (default: 6)
     * @return array Array of random meal recipes
     */
    public function getRandomMeals(int $count = 6): array
    {
        $cacheKey = "random_meals_{$count}";
        
        // Cache for shorter time (15 minutes) to ensure freshness
        return Cache::remember($cacheKey, 900, function () use ($count) {
            $meals = [];
            $mealIds = [];
            
            // Fetch random meals one by one to avoid duplicates
            for ($i = 0; $i < $count * 2; $i++) { // Try more times to account for duplicates
                if (count($meals) >= $count) {
                    break;
                }
                
                try {
                    $response = Http::get($this->baseUrl . 'random.php');
                    
                    if ($response->successful()) {
                        $data = $response->json();
                        $meal = $data['meals'][0] ?? null;
                        
                        if ($meal && isset($meal['idMeal'])) {
                            $mealId = $meal['idMeal'];
                            
                            // Avoid duplicates
                            if (!in_array($mealId, $mealIds)) {
                                $mealIds[] = $mealId;
                                $meals[] = $meal;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error fetching random meal from TheMealDB', [
                        'attempt' => $i + 1,
                        'error' => $e->getMessage()
                    ]);
                }
                
                // Small delay to avoid rate limiting
                if ($i < $count * 2 - 1) {
                    usleep(100000); // 0.1 second delay
                }
            }
            
            return $meals;
        });
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
