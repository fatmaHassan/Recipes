<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomRecipe>
 */
class CustomRecipeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'instructions' => fake()->paragraph(),
            'category' => fake()->optional()->word(),
            'area' => fake()->optional()->country(),
            'image_url' => null,
            'servings' => (string) fake()->numberBetween(1, 8),
            'prep_time_minutes' => fake()->optional()->numberBetween(5, 60),
            'cook_time_minutes' => fake()->optional()->numberBetween(10, 120),
        ];
    }
}
