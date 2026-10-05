<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\SavedRecipe;
use App\Models\User;
use App\Support\Backfills\BackfillSavedRecipeFk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavedRecipesFkTest extends TestCase
{
    use RefreshDatabase;

    private function seedRecipe(string $id, string $title): Recipe
    {
        return Recipe::create([
            'external_id' => $id,
            'title' => $title,
            'area' => 'Japanese',
            'category' => 'Chicken',
            'instructions' => "Instructions for {$title}",
            'thumb_url' => "https://www.themealdb.com/images/media/meals/{$id}.jpg",
            'is_active' => true,
        ]);
    }

    private function verifiedUser(): User
    {
        $user = User::factory()->create();
        $user->markEmailAsVerified();

        return $user;
    }

    public function test_save_links_saved_recipe_to_internal_recipe_in_db_mode(): void
    {
        config(['recipes.source' => 'db']);
        $recipe = $this->seedRecipe('52772', 'Teriyaki Chicken Casserole');
        $user = $this->verifiedUser();

        $response = $this->actingAs($user)->post('/recipes/save', [
            'recipe_id' => '52772',
            'recipe_data' => ['idMeal' => '52772', 'strMeal' => 'Teriyaki Chicken Casserole'],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('saved_recipes', [
            'user_id' => $user->id,
            'recipe_id' => '52772',
            'recipe_internal_id' => $recipe->id,
        ]);
        $this->assertSame($recipe->id, $user->savedRecipes()->first()->recipe->id);
    }

    public function test_save_resolves_recipe_data_and_fk_from_database_when_absent(): void
    {
        config(['recipes.source' => 'db']);
        $recipe = $this->seedRecipe('52772', 'Teriyaki Chicken Casserole');
        $user = $this->verifiedUser();

        $response = $this->actingAs($user)->postJson('/api/recipes/save', [
            'recipe_id' => '52772',
        ]);

        $response->assertStatus(201);

        $saved = SavedRecipe::where('user_id', $user->id)->first();
        $this->assertSame($recipe->id, $saved->recipe_internal_id);
        $this->assertSame('52772', $saved->recipe_data['idMeal']);
        $this->assertSame('Teriyaki Chicken Casserole', $saved->recipe_data['strMeal']);
    }

    public function test_save_returns_404_when_recipe_unresolvable_in_db_mode(): void
    {
        config(['recipes.source' => 'db']);
        $user = $this->verifiedUser();

        $this->actingAs($user)
            ->postJson('/api/recipes/save', ['recipe_id' => '99999'])
            ->assertStatus(404);

        $this->assertDatabaseCount('saved_recipes', 0);
    }

    public function test_save_in_api_mode_still_works_without_internal_recipe(): void
    {
        $user = $this->verifiedUser();

        $response = $this->actingAs($user)->postJson('/api/recipes/save', [
            'recipe_id' => '52772',
            'recipe_data' => ['idMeal' => '52772', 'strMeal' => 'From TheMealDB'],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('saved_recipes', [
            'user_id' => $user->id,
            'recipe_id' => '52772',
            'recipe_internal_id' => null,
        ]);
    }

    public function test_backfill_links_existing_saved_recipes(): void
    {
        $recipe = $this->seedRecipe('52772', 'Teriyaki Chicken Casserole');
        $user = $this->verifiedUser();

        $matching = SavedRecipe::create([
            'user_id' => $user->id,
            'recipe_id' => '52772',
            'recipe_data' => ['idMeal' => '52772'],
            'is_favorite' => false,
        ]);
        $orphan = SavedRecipe::create([
            'user_id' => $user->id,
            'recipe_id' => 'no-such-meal',
            'recipe_data' => ['idMeal' => 'no-such-meal'],
            'is_favorite' => false,
        ]);

        $linked = BackfillSavedRecipeFk::run();

        $this->assertSame(1, $linked);
        $this->assertSame($recipe->id, $matching->refresh()->recipe_internal_id);
        $this->assertNull($orphan->refresh()->recipe_internal_id);
    }

    public function test_toggle_favorite_still_works(): void
    {
        config(['recipes.source' => 'db']);
        $this->seedRecipe('52772', 'Teriyaki Chicken Casserole');
        $user = $this->verifiedUser();

        $this->actingAs($user)->postJson('/api/recipes/save', [
            'recipe_id' => '52772',
            'favorite' => '1',
        ])->assertStatus(201);

        $this->assertTrue($user->savedRecipes()->first()->is_favorite);

        $this->actingAs($user)
            ->postJson('/api/recipes/52772/favorite')
            ->assertStatus(200);

        $this->assertFalse($user->savedRecipes()->first()->is_favorite);
    }
}
