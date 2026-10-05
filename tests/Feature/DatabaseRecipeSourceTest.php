<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseRecipeSourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['recipes.source' => 'db']);
    }

    private function seedRecipe(string $id, string $title, string $area, array $ingredients = []): Recipe
    {
        $recipe = Recipe::factory()->create([
            'external_id' => $id,
            'title' => $title,
            'area' => $area,
            'category' => 'Chicken',
            'instructions' => "Instructions for {$title}",
            'thumb_url' => "https://www.themealdb.com/images/media/meals/{$id}.jpg",
        ]);

        $recipe->ingredients()->createMany(collect($ingredients)
            ->map(fn ($ingredient, $index) => [
                'sort_order' => $index + 1,
                'name' => $ingredient[0],
                'measure' => $ingredient[1],
            ])
            ->all());

        return $recipe;
    }

    private function verifiedUser(): User
    {
        $user = User::factory()->create();
        $user->markEmailAsVerified();

        return $user;
    }

    public function test_details_are_served_from_database(): void
    {
        $this->seedRecipe('52772', 'Teriyaki Chicken Casserole', 'Japanese', [
            ['soy sauce', '3/4 cup'],
            ['chicken', '2 lbs'],
        ]);

        $response = $this->get('/recipes/52772');

        $response->assertStatus(200);
        $response->assertViewIs('recipes.show');
        $response->assertViewHas('recipe', fn ($recipe) => $recipe['idMeal'] === '52772'
            && $recipe['strMeal'] === 'Teriyaki Chicken Casserole'
            && $recipe['strArea'] === 'Japanese'
            && $recipe['strInstructions'] === 'Instructions for Teriyaki Chicken Casserole'
            && $recipe['strIngredient1'] === 'soy sauce'
            && $recipe['strMeasure1'] === '3/4 cup'
            && $recipe['strIngredient2'] === 'chicken'
            && $recipe['strMeasure2'] === '2 lbs'
            && $recipe['strIngredient3'] === ''
            && $recipe['strMeasure20'] === '');

        $response->assertSee('Instructions for Teriyaki Chicken Casserole');
        $response->assertSee('soy sauce');
    }

    public function test_missing_recipe_returns_404(): void
    {
        $this->get('/recipes/99999')->assertStatus(404);
    }

    public function test_search_by_ingredient_returns_matching_summaries(): void
    {
        $this->seedRecipe('52772', 'Teriyaki Chicken Casserole', 'Japanese', [
            ['chicken', '2 lbs'],
        ]);
        $this->seedRecipe('52819', 'Chicken Handi', 'Indian', [
            ['chicken', '1 kg'],
        ]);
        $this->seedRecipe('53000', 'Tarte Tatin', 'French', [
            ['flour', '200g'],
        ]);

        $response = $this->actingAs($this->verifiedUser())
            ->get(route('recipes.search', ['ingredients' => ['chicken']]));

        $response->assertStatus(200);
        $response->assertViewIs('recipes.index');
        $response->assertViewHas('paginatedRecipes', fn ($paginated) => $paginated->count() === 2
            && collect($paginated->items())->every(
                fn ($recipe) => isset($recipe['idMeal'], $recipe['strMeal'], $recipe['strMealThumb'])
            ));
    }

    public function test_random_meals_return_details_shape(): void
    {
        $this->seedRecipe('52772', 'Teriyaki Chicken Casserole', 'Japanese', [
            ['chicken', '2 lbs'],
        ]);

        $response = $this->getJson('/api/recipes/random?count=1');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'recipes')
            ->assertJsonStructure([
                'recipes' => [
                    ['idMeal', 'strMeal', 'strInstructions', 'strMealThumb', 'strIngredient1', 'strMeasure1'],
                ],
            ]);
    }

    public function test_cuisines_and_cuisine_recipes_are_served_from_database(): void
    {
        $this->seedRecipe('52772', 'Teriyaki Chicken Casserole', 'Japanese');

        $index = $this->getJson('/api/cuisines');

        $index->assertStatus(200)
            ->assertJsonFragment(['name' => 'Japanese', 'code' => 'jp']);

        $show = $this->getJson('/api/cuisines/Japanese');

        $show->assertStatus(200)
            ->assertJsonCount(1, 'recipes')
            ->assertJsonFragment(['idMeal' => '52772', 'strMeal' => 'Teriyaki Chicken Casserole']);

        $this->getJson('/api/cuisines/Nowhere')->assertJsonFragment(['count' => 0]);
    }

    public function test_allergy_filter_excludes_matching_recipes_in_single_query(): void
    {
        $user = $this->verifiedUser();
        $user->allergies()->create(['allergen_name' => 'butter']);

        $this->seedRecipe('52772', 'Butter Chicken', 'Indian', [
            ['butter', '2 tbsp'],
            ['chicken', '2 lbs'],
        ]);
        $safe = $this->seedRecipe('52819', 'Teriyaki Chicken', 'Japanese', [
            ['soy sauce', '1 cup'],
            ['chicken', '2 lbs'],
        ]);

        $response = $this->actingAs($user)
            ->get(route('recipes.search', ['ingredients' => ['chicken']]));

        $response->assertStatus(200);
        $response->assertViewHas('paginatedRecipes', function ($paginated) use ($safe) {
            $items = collect($paginated->items());

            return $items->count() === 1 && $items->first()['idMeal'] === $safe->external_id;
        });
    }

    public function test_ingredient_autocomplete_serves_from_database(): void
    {
        $this->seedRecipe('52772', 'Teriyaki Chicken', 'Japanese', [
            ['Chicken breast', '500g'],
        ]);
        $this->seedRecipe('52819', 'Chicken Handi', 'Indian', [
            ['chickpeas', '1 cup'],
        ]);

        $user = $this->verifiedUser();

        $this->actingAs($user)
            ->getJson('/api/ingredients/search?q=chick')
            ->assertStatus(200)
            ->assertJsonFragment(['suggestions' => ['Chicken breast', 'chickpeas']]);

        $this->actingAs($user)
            ->postJson('/api/ingredients/check', ['ingredient' => 'chickpeas'])
            ->assertStatus(200)
            ->assertJsonFragment(['exists' => true]);
    }

    public function test_inactive_recipes_are_not_served(): void
    {
        $recipe = $this->seedRecipe('52772', 'Hidden Recipe', 'Japanese', [
            ['chicken', '2 lbs'],
        ]);
        $recipe->update(['is_active' => false]);

        $this->get('/recipes/52772')->assertStatus(404);
        $this->getJson('/api/cuisines/Japanese')->assertJsonFragment(['count' => 0]);
        $this->getJson('/api/recipes/random?count=5')->assertJsonCount(0, 'recipes');
    }
}
