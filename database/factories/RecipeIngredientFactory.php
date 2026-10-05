<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RecipeIngredientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sort_order' => $this->faker->numberBetween(1, 20),
            'name' => ucfirst($this->faker->words(2, true)),
            'measure' => $this->faker->randomElement(['1 cup', '2 tbsp', '500g', '1 tsp', null]),
        ];
    }
}
