<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredientApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test ingredient creation and retrieval.
     */
    public function test_ingredient_creation_and_retrieval(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create ingredient
        $response = $this->post(route('ingredients.store'), [
            'name' => 'Chicken',
        ]);

        $response->assertRedirect(route('ingredients.index'));
        $response->assertSessionHas('success');

        // Verify ingredient was created
        $this->assertDatabaseHas('ingredients', [
            'user_id' => $user->id,
            'name' => 'Chicken',
        ]);

        // Retrieve ingredients
        $response = $this->get(route('ingredients.index'));
        $response->assertStatus(200);
        $response->assertViewIs('ingredients.index');
        $response->assertViewHas('ingredients');
    }

    /**
     * Test ingredient deletion.
     */
    public function test_ingredient_deletion(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $ingredient = Ingredient::factory()->create([
            'user_id' => $user->id,
            'name' => 'Tomato',
        ]);

        $response = $this->delete(route('ingredients.destroy', $ingredient));

        $response->assertRedirect(route('ingredients.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('ingredients', [
            'id' => $ingredient->id,
        ]);
    }
}
