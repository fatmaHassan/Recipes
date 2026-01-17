<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Allergy>
 */
class AllergyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $allergens = ['peanuts', 'dairy', 'gluten', 'eggs', 'shellfish', 'soy', 'tree nuts'];
        
        return [
            'user_id' => \App\Models\User::factory(),
            'allergen_name' => fake()->randomElement($allergens),
        ];
    }
}
