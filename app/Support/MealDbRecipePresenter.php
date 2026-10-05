<?php

namespace App\Support;

use App\Models\Recipe;

class MealDbRecipePresenter
{
    public static function summary(Recipe $recipe): array
    {
        return [
            'idMeal' => $recipe->external_id,
            'strMeal' => $recipe->title,
            'strMealThumb' => $recipe->thumb_url,
        ];
    }

    public static function details(Recipe $recipe): array
    {
        $details = [
            'idMeal' => $recipe->external_id,
            'strMeal' => $recipe->title,
            'strDrinkAlternate' => null,
            'strCategory' => $recipe->category,
            'strArea' => $recipe->area,
            'strInstructions' => $recipe->instructions,
            'strMealThumb' => $recipe->thumb_url,
            'strTags' => $recipe->tags,
            'strYoutube' => $recipe->youtube_url,
            'strSource' => $recipe->source_url,
            'strImageSource' => null,
            'strCreativeCommonsConfirmed' => null,
            'dateModified' => null,
        ];

        $ingredients = $recipe->relationLoaded('ingredients')
            ? $recipe->ingredients
            : $recipe->ingredients()->get();

        foreach ($ingredients as $ingredient) {
            $details["strIngredient{$ingredient->sort_order}"] = $ingredient->name;
            $details["strMeasure{$ingredient->sort_order}"] = $ingredient->measure ?? '';
        }

        for ($i = 1; $i <= 20; $i++) {
            $details["strIngredient{$i}"] ??= '';
            $details["strMeasure{$i}"] ??= '';
        }

        return $details;
    }
}
