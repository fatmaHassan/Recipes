<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SavedRecipe>
 */
class SavedRecipeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'recipe_id' => fake()->numerify('#####'),
            'recipe_data' => [
                'idMeal' => fake()->numerify('#####'),
                'strMeal' => fake()->words(3, true),
                'strMealThumb' => fake()->imageUrl(),
            ],
            'is_favorite' => false,
        ];
    }
}
