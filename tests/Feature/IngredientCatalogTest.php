<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IngredientCatalogTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /**
     * Stub the TheMealDB ingredient list endpoint with a small catalog.
     * Http::fake() merges stubs instead of replacing them, so this must be
     * called at most once per test.
     */
    private function fakeIngredientList(int $status = 200): void
    {
        Http::fake([
            // Base-URL-agnostic: matches the free v1 key and the premium v2 path.
            '*list.php*' => Http::response([
                'meals' => [
                    ['idIngredient' => '1', 'strIngredient' => 'Chicken', 'strThumb' => 'https://www.themealdb.com/images/ingredients/chicken.png'],
                    ['idIngredient' => '2', 'strIngredient' => 'Milk', 'strThumb' => null],
                    ['idIngredient' => '3', 'strIngredient' => 'Beef Stock', 'strThumb' => 'https://www.themealdb.com/images/ingredients/beef%20stock.png'],
                    ['idIngredient' => '4', 'strIngredient' => 'Soy Sauce', 'strThumb' => 'https://www.themealdb.com/images/ingredients/soy%20sauce.png'],
                    ['idIngredient' => '5', 'strIngredient' => '', 'strThumb' => null],
                ],
            ], $status),
        ]);
    }

    public function test_index_shows_the_full_catalog_with_categories(): void
    {
        $this->fakeIngredientList();

        $response = $this->get(route('ingredients.index'));

        $response->assertStatus(200);
        $response->assertViewIs('ingredients.index');
        $response->assertViewHas('ingredients');
        $response->assertViewHas('categories');
        $response->assertViewHas('catalog', fn ($catalog) => count($catalog) === 4);
        $response->assertSee('Browse Ingredients');
    }

    public function test_bulk_store_adds_multiple_catalog_ingredients(): void
    {
        $this->fakeIngredientList();

        $response = $this->post(route('ingredients.store'), [
            'names' => ['Chicken', 'Milk'],
        ]);

        $response->assertRedirect(route('ingredients.index'));
        $response->assertSessionHas('success', fn ($message) => str_contains($message, '2 ingredients added'));

        $this->assertDatabaseHas('ingredients', ['user_id' => $this->user->id, 'name' => 'Chicken']);
        $this->assertDatabaseHas('ingredients', ['user_id' => $this->user->id, 'name' => 'Milk']);
    }

    public function test_bulk_store_deduplicates_within_the_request(): void
    {
        $this->fakeIngredientList();

        $this->post(route('ingredients.store'), [
            'names' => ['Chicken', 'chicken', 'CHICKEN'],
        ]);

        $this->assertDatabaseCount('ingredients', 1);
        $this->assertDatabaseHas('ingredients', ['user_id' => $this->user->id, 'name' => 'Chicken']);
    }

    public function test_bulk_store_skips_ingredients_the_user_already_has(): void
    {
        $this->fakeIngredientList();

        Ingredient::factory()->create(['user_id' => $this->user->id, 'name' => 'Chicken']);

        $response = $this->post(route('ingredients.store'), [
            'names' => ['Chicken', 'Milk'],
        ]);

        $response->assertRedirect(route('ingredients.index'));
        $response->assertSessionHas('success', fn ($message) => str_contains($message, '1 ingredient added') && str_contains($message, '1 already'));

        $this->assertDatabaseCount('ingredients', 2);
    }

    public function test_bulk_store_reports_when_everything_is_already_stored(): void
    {
        $this->fakeIngredientList();

        Ingredient::factory()->create(['user_id' => $this->user->id, 'name' => 'Chicken']);

        $response = $this->post(route('ingredients.store'), [
            'names' => ['Chicken'],
        ]);

        $response->assertRedirect(route('ingredients.index'));
        $response->assertSessionHas('success', fn ($message) => str_contains($message, 'already in your list'));

        $this->assertDatabaseCount('ingredients', 1);
    }

    public function test_bulk_store_rejects_names_outside_the_catalog(): void
    {
        $this->fakeIngredientList();

        $response = $this->post(route('ingredients.store'), [
            'names' => ['Chicken', 'Mystery Custom Ingredient'],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('names');

        $this->assertDatabaseCount('ingredients', 0);
    }

    public function test_bulk_store_caps_the_request_at_fifty_names(): void
    {
        $this->fakeIngredientList();

        $response = $this->post(route('ingredients.store'), [
            'names' => array_fill(0, 51, 'Chicken'),
        ]);

        $response->assertSessionHasErrors('names');

        $this->assertDatabaseCount('ingredients', 0);
    }

    public function test_single_name_store_still_works_for_custom_ingredients(): void
    {
        $response = $this->post(route('ingredients.store'), [
            'name' => 'Mystery Custom Ingredient',
        ]);

        $response->assertRedirect(route('ingredients.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ingredients', [
            'user_id' => $this->user->id,
            'name' => 'Mystery Custom Ingredient',
        ]);
    }

    public function test_index_degrades_gracefully_when_the_catalog_cannot_be_fetched(): void
    {
        $this->fakeIngredientList(500);

        $response = $this->get(route('ingredients.index'));

        $response->assertStatus(200);
        $response->assertViewHas('catalog', []);
        $response->assertSee('load the TheMealDB ingredient catalog');
    }
}
