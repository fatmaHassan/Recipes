<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RecipeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'external_id' => (string) $this->faker->unique()->numberBetween(50000, 99999),
            'source' => 'themealdb',
            'title' => ucfirst($this->faker->words(3, true)).' '.$this->faker->randomElement(['Casserole', 'Curry', 'Salad', 'Pasta', 'Soup']),
            'category' => $this->faker->randomElement(['Chicken', 'Beef', 'Seafood', 'Dessert', 'Vegetarian']),
            'area' => $this->faker->randomElement(['Japanese', 'Italian', 'Indian', 'French', 'Chinese', 'Mexican']),
            'instructions' => $this->faker->paragraphs(2, true),
            'thumb_url' => 'https://www.themealdb.com/images/media/meals/'.$this->faker->slug().'.jpg',
            'tags' => $this->faker->randomElement(['Chicken,GlutenFree', 'Pasta', null, 'Dessert']),
            'youtube_url' => null,
            'source_url' => null,
            'is_active' => true,
            'data_hash' => $this->faker->md5,
            'last_synced_at' => now(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
