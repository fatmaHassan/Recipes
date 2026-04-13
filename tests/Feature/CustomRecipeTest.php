<?php

namespace Tests\Feature;

use App\Models\CustomRecipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomRecipeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_custom_recipe_form(): void
    {
        $response = $this->get(route('custom-recipes.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('custom-recipes.create'));

        $response->assertOk();
        $response->assertSee('Add custom recipe', false);
    }

    public function test_authenticated_user_can_store_custom_recipe_with_ingredients(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('custom-recipes.store'), [
            'title' => 'Grandma soup',
            'instructions' => "Boil water.\nAdd love.",
            'category' => 'Soup',
            'area' => 'Home',
            'image_url' => 'https://example.com/image.jpg',
            'servings' => '4',
            'prep_time_minutes' => 10,
            'cook_time_minutes' => 30,
            'ingredients' => [
                ['name' => 'Water', 'measure' => '1 L'],
                ['name' => 'Salt', 'measure' => 'pinch'],
                ['name' => '', 'measure' => ''],
            ],
        ]);

        $recipe = CustomRecipe::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($recipe);
        $response->assertRedirect(route('custom-recipes.show', $recipe));
        $response->assertSessionHas('success');

        $this->assertSame('Grandma soup', $recipe->title);
        $this->assertSame('Soup', $recipe->category);
        $this->assertCount(2, $recipe->ingredients);
        $this->assertSame('Water', $recipe->ingredients[0]->name);
        $this->assertSame('1 L', $recipe->ingredients[0]->measure);
    }

    public function test_store_requires_at_least_one_ingredient(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('custom-recipes.store'), [
            'title' => 'Empty',
            'instructions' => 'Nothing',
            'ingredients' => [
                ['name' => '  ', 'measure' => ''],
            ],
        ]);

        $response->assertSessionHasErrors('ingredients');
        $this->assertSame(0, CustomRecipe::query()->count());
    }

    public function test_authenticated_user_can_list_their_custom_recipes(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $mine = CustomRecipe::factory()->for($user)->create(['title' => 'Mine']);
        CustomRecipe::factory()->for($other)->create(['title' => 'Theirs']);

        $response = $this->actingAs($user)->get(route('custom-recipes.index'));

        $response->assertOk();
        $response->assertSee('Mine', false);
        $response->assertDontSee('Theirs', false);
    }

    public function test_authenticated_user_can_view_their_custom_recipe(): void
    {
        $user = User::factory()->create();
        $recipe = CustomRecipe::factory()->for($user)->create([
            'title' => 'Pasta',
            'instructions' => 'Cook it.',
        ]);

        $response = $this->actingAs($user)->get(route('custom-recipes.show', $recipe));

        $response->assertOk();
        $response->assertSee('Pasta', false);
        $response->assertSee('Cook it.', false);
    }

    public function test_user_cannot_view_another_users_custom_recipe(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $recipe = CustomRecipe::factory()->for($owner)->create();

        $response = $this->actingAs($intruder)->get(route('custom-recipes.show', $recipe));

        $response->assertForbidden();
    }

    public function test_authenticated_user_can_update_custom_recipe(): void
    {
        $user = User::factory()->create();
        $recipe = CustomRecipe::factory()->for($user)->create([
            'title' => 'Old title',
            'instructions' => 'Old steps',
        ]);
        $recipe->ingredients()->create(['sort_order' => 0, 'name' => 'Flour', 'measure' => '1 cup']);

        $response = $this->actingAs($user)->patch(route('custom-recipes.update', $recipe), [
            'title' => 'New title',
            'instructions' => 'New steps',
            'category' => 'Bakery',
            'area' => null,
            'image_url' => null,
            'servings' => '2',
            'prep_time_minutes' => null,
            'cook_time_minutes' => null,
            'ingredients' => [
                ['name' => 'Sugar', 'measure' => '2 tbsp'],
            ],
        ]);

        $response->assertRedirect(route('custom-recipes.show', $recipe));
        $response->assertSessionHas('success');

        $recipe->refresh();
        $this->assertSame('New title', $recipe->title);
        $this->assertSame('Bakery', $recipe->category);
        $this->assertCount(1, $recipe->ingredients);
        $this->assertSame('Sugar', $recipe->ingredients->first()->name);
    }
}
