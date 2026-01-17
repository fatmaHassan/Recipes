<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RecipeApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test recipe search by ingredients returns results.
     */
    public function test_recipe_search_by_ingredients_returns_results(): void
    {
        // Mock TheMealDB API response
        Http::fake([
            'www.themealdb.com/api/json/v1/1/filter.php*' => Http::response([
                'meals' => [
                    [
                        'idMeal' => '52772',
                        'strMeal' => 'Teriyaki Chicken Casserole',
                        'strMealThumb' => 'https://www.themealdb.com/images/media/meals/wvpsxx1468256321.jpg',
                    ],
                ],
            ], 200),
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('recipes.search'), [
            'ingredients' => ['chicken'],
        ]);

        $response->assertStatus(200);
        $response->assertViewIs('recipes.index');
        $response->assertViewHas('recipes');
    }

    /**
     * Test recipe details endpoint returns recipe information.
     */
    public function test_recipe_details_endpoint_returns_recipe(): void
    {
        // Mock TheMealDB API response
        Http::fake([
            'www.themealdb.com/api/json/v1/1/lookup.php*' => Http::response([
                'meals' => [
                    [
                        'idMeal' => '52772',
                        'strMeal' => 'Teriyaki Chicken Casserole',
                        'strMealThumb' => 'https://www.themealdb.com/images/media/meals/wvpsxx1468256321.jpg',
                        'strInstructions' => 'Test instructions',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->get(route('recipes.show', '52772'));

        $response->assertStatus(200);
        $response->assertViewIs('recipes.show');
        $response->assertViewHas('recipe');
    }
}
