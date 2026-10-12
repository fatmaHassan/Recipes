<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\SavedRecipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavedRecipeDisplayTest extends TestCase
{
    use RefreshDatabase;

    private function verifiedUser(): User
    {
        $user = User::factory()->create();
        $user->markEmailAsVerified();

        return $user;
    }

    private function linkedSavedRecipe(User $user): SavedRecipe
    {
        $recipe = Recipe::factory()->create([
            'external_id' => '52772',
            'title' => 'Teriyaki Chicken Casserole',
            'area' => 'Japanese',
            'category' => 'Chicken',
            'instructions' => 'Instructions from the database',
            'thumb_url' => 'https://www.themealdb.com/images/media/meals/db.jpg',
        ]);

        return SavedRecipe::create([
            'user_id' => $user->id,
            'recipe_id' => '52772',
            'recipe_internal_id' => $recipe->id,
            'recipe_data' => null,
            'is_favorite' => true,
        ]);
    }

    public function test_my_recipes_page_renders_from_linked_recipe(): void
    {
        $user = $this->verifiedUser();
        $this->linkedSavedRecipe($user);

        $response = $this->actingAs($user)->get('/my-recipes');

        $response->assertStatus(200);
        $response->assertSee('Teriyaki Chicken Casserole');
        $response->assertSee('https://www.themealdb.com/images/media/meals/db.jpg');
    }

    public function test_my_recipes_falls_back_to_snapshot_for_orphan_rows(): void
    {
        $user = $this->verifiedUser();

        SavedRecipe::create([
            'user_id' => $user->id,
            'recipe_id' => 'no-such-meal',
            'recipe_internal_id' => null,
            'recipe_data' => [
                'idMeal' => 'no-such-meal',
                'strMeal' => 'Snapshot Meal',
                'strMealThumb' => 'https://www.themealdb.com/images/media/meals/snapshot.jpg',
            ],
            'is_favorite' => false,
        ]);

        $response = $this->actingAs($user)->get('/my-recipes');

        $response->assertStatus(200);
        $response->assertSee('Snapshot Meal');
        $response->assertSee('https://www.themealdb.com/images/media/meals/snapshot.jpg');
    }

    public function test_favorites_page_renders_from_linked_recipe(): void
    {
        $user = $this->verifiedUser();
        $this->linkedSavedRecipe($user);

        $response = $this->actingAs($user)->get('/favorites');

        $response->assertStatus(200);
        $response->assertSee('Teriyaki Chicken Casserole');
    }

    public function test_api_my_recipes_serves_linked_recipe_details(): void
    {
        $user = $this->verifiedUser();
        $this->linkedSavedRecipe($user);

        $response = $this->actingAs($user)->getJson('/api/my-recipes');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => '52772',
                'idMeal' => '52772',
                'strMeal' => 'Teriyaki Chicken Casserole',
                'is_favorite' => true,
            ]);

        $recipe = $response->json('recipes.0');
        $this->assertSame('Instructions from the database', $recipe['strInstructions']);
        $this->assertArrayHasKey('saved_id', $recipe);
        $this->assertArrayHasKey('saved_at', $recipe);
    }

    public function test_api_my_recipes_falls_back_to_snapshot_for_orphan_rows(): void
    {
        $user = $this->verifiedUser();

        SavedRecipe::create([
            'user_id' => $user->id,
            'recipe_id' => 'no-such-meal',
            'recipe_internal_id' => null,
            'recipe_data' => [
                'idMeal' => 'no-such-meal',
                'strMeal' => 'Snapshot Meal',
            ],
            'is_favorite' => false,
        ]);

        $this->actingAs($user)
            ->getJson('/api/my-recipes')
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => 'no-such-meal',
                'idMeal' => 'no-such-meal',
                'strMeal' => 'Snapshot Meal',
            ]);
    }
}
