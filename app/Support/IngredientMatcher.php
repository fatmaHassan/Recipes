<?php

namespace App\Support;

class IngredientMatcher
{
    private const FALLBACK_SUGGESTIONS = [
        'meat' => ['chicken', 'beef', 'pork', 'lamb', 'turkey'],
        'chicken' => ['chicken breast', 'chicken thigh', 'chicken wing'],
        'beef' => ['ground beef', 'beef steak', 'beef roast'],
        'fish' => ['salmon', 'tuna', 'cod', 'tilapia'],
        'vegetable' => ['carrot', 'onion', 'tomato', 'potato', 'broccoli'],
        'dairy' => ['milk', 'cheese', 'butter', 'cream'],
    ];

    public static function search(array $all, string $query, int $limit = 10): array
    {
        $queryLower = strtolower(trim($query));

        if ($queryLower === '') {
            return array_slice($all, 0, $limit);
        }

        $exact = [];
        $startsWith = [];
        $contains = [];

        foreach ($all as $ingredient) {
            $ingredientLower = strtolower($ingredient);

            if ($ingredientLower === $queryLower) {
                $exact[] = $ingredient;
            } elseif (str_starts_with($ingredientLower, $queryLower)) {
                $startsWith[] = $ingredient;
            } elseif (str_contains($ingredientLower, $queryLower)) {
                $contains[] = $ingredient;
            }
        }

        return array_slice(array_merge($exact, $startsWith, $contains), 0, $limit);
    }

    public static function suggestions(array $all, string $ingredient): array
    {
        $ingredientLower = strtolower(trim($ingredient));
        $suggestions = [];

        foreach ($all as $candidate) {
            $candidateLower = strtolower($candidate);

            if ($candidateLower === $ingredientLower) {
                continue;
            }

            if (str_contains($candidateLower, $ingredientLower)
                || str_contains($ingredientLower, $candidateLower)
                || similar_text($ingredientLower, $candidateLower) / max(strlen($ingredientLower), strlen($candidateLower)) > 0.6) {
                $suggestions[] = $candidate;
            }
        }

        $suggestions = array_slice($suggestions, 0, 5);

        if (empty($suggestions) && isset(self::FALLBACK_SUGGESTIONS[$ingredientLower])) {
            return self::FALLBACK_SUGGESTIONS[$ingredientLower];
        }

        return $suggestions;
    }
}
