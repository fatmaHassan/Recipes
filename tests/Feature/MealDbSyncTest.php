<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Services\MealDbSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MealDbSyncTest extends TestCase
{
    use RefreshDatabase;

    private array $meals = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->meals = [
            '52772' => [
                'idMeal' => '52772',
                'strMeal' => 'Teriyaki Chicken Casserole',
                'strCategory' => 'Chicken',
                'strArea' => 'Japanese',
                'strInstructions' => 'Test instructions',
                'strMealThumb' => 'https://www.themealdb.com/images/media/meals/wvpsxx1468256321.jpg',
                'strTags' => 'Chicken, teriyaki',
                'strYoutube' => 'https://www.youtube.com/watch?v=test',
                'strSource' => null,
                'strIngredient1' => 'soy sauce',
                'strMeasure1' => '3/4 cup',
                'strIngredient2' => 'chicken',
                'strMeasure2' => '2 lbs',
            ],
            '52819' => [
                'idMeal' => '52819',
                'strMeal' => 'Tarte Tatin',
                'strCategory' => 'Dessert',
                'strArea' => 'Italian',
                'strInstructions' => 'Test dessert instructions',
                'strMealThumb' => 'https://www.themealdb.com/images/media/meals/test.jpg',
                'strIngredient1' => 'flour',
                'strMeasure1' => '200g',
                'strIngredient2' => 'sugar',
                'strMeasure2' => '100g',
            ],
        ];
    }

    private function fakeThemealdb(): void
    {
        Http::fake(function (Request $request) {
            $url = $request->url();

            if (str_contains($url, '/list.php')) {
                if ($request['a'] ?? null) {
                    return Http::response(['meals' => [
                        ['strArea' => 'Japanese'],
                        ['strArea' => 'Italian'],
                    ]]);
                }

                if ($request['c'] ?? null) {
                    return Http::response(['meals' => [
                        ['strCategory' => 'Chicken'],
                        ['strCategory' => 'Dessert'],
                    ]]);
                }
            }

            if (str_contains($url, '/filter.php')) {
                $area = $request['a'] ?? null;
                $category = $request['c'] ?? null;

                $meals = collect($this->meals)
                    ->filter(fn ($meal) => $meal['strArea'] === $area || $meal['strCategory'] === $category)
                    ->map(fn ($meal) => [
                        'idMeal' => $meal['idMeal'],
                        'strMeal' => $meal['strMeal'],
                        'strMealThumb' => $meal['strMealThumb'],
                    ])
                    ->unique('idMeal')
                    ->values()
                    ->all();

                return Http::response(['meals' => $meals ?: null]);
            }

            if (str_contains($url, '/lookup.php')) {
                $meal = $this->meals[$request['i']] ?? null;

                return Http::response(['meals' => $meal ? [$meal] : null]);
            }

            return Http::response([], 404);
        });
    }

    public function test_sync_inserts_recipes_with_ingredients(): void
    {
        $this->fakeThemealdb();

        $stats = app(MealDbSyncService::class)->sync();

        $this->assertSame([
            'inserted' => 2,
            'updated' => 0,
            'skipped' => 0,
            'deactivated' => 0,
            'failed' => 0,
        ], $stats);

        $recipe = Recipe::where('external_id', '52772')->first();
        $this->assertNotNull($recipe);
        $this->assertSame('Teriyaki Chicken Casserole', $recipe->title);
        $this->assertSame('Japanese', $recipe->area);
        $this->assertSame('Chicken', $recipe->category);
        $this->assertTrue($recipe->is_active);
        $this->assertNotNull($recipe->data_hash);
        $this->assertNotNull($recipe->last_synced_at);

        $this->assertSame(['soy sauce', 'chicken'], $recipe->ingredients->pluck('name')->all());
        $this->assertSame(['soy sauce', 'chicken'], $recipe->ingredients->pluck('name_normalized')->all());
        $this->assertSame('3/4 cup', $recipe->ingredients[0]->measure);
        $this->assertSame(1, $recipe->ingredients[0]->sort_order);
        $this->assertSame(2, $recipe->ingredients[1]->sort_order);
    }

    public function test_sync_skips_unchanged_recipes_on_second_run(): void
    {
        $this->fakeThemealdb();
        $service = app(MealDbSyncService::class);

        $service->sync();
        $stats = $service->sync();

        $this->assertSame(0, $stats['inserted']);
        $this->assertSame(0, $stats['updated']);
        $this->assertSame(2, $stats['skipped']);
        $this->assertSame(0, $stats['failed']);
        $this->assertSame(2, Recipe::count());
        $this->assertSame(4, RecipeIngredient::count());
    }

    public function test_sync_updates_changed_recipe_and_replaces_ingredients(): void
    {
        $this->fakeThemealdb();
        $service = app(MealDbSyncService::class);

        $service->sync();

        $this->meals['52772']['strInstructions'] = 'Updated instructions';
        $this->meals['52772']['strIngredient2'] = 'tofu';
        $this->meals['52772']['strMeasure2'] = '1 lb';

        $stats = $service->sync();

        $this->assertSame(1, $stats['updated']);
        $this->assertSame(1, $stats['skipped']);

        $recipe = Recipe::where('external_id', '52772')->first();
        $this->assertSame('Updated instructions', $recipe->instructions);
        $this->assertSame(['soy sauce', 'tofu'], $recipe->ingredients->pluck('name')->all());
        $this->assertSame('1 lb', $recipe->ingredients[1]->measure);
        $this->assertSame(2, RecipeIngredient::where('recipe_id', $recipe->id)->count());
    }

    public function test_sync_deactivates_recipes_missing_from_api(): void
    {
        $this->fakeThemealdb();
        $service = app(MealDbSyncService::class);

        $service->sync();

        unset($this->meals['52819']);
        $stats = $service->sync();

        $this->assertSame(1, $stats['deactivated']);
        $this->assertTrue((bool) Recipe::where('external_id', '52772')->value('is_active'));
        $this->assertFalse((bool) Recipe::where('external_id', '52819')->value('is_active'));
    }

    public function test_sync_with_area_only_enumerates_that_area(): void
    {
        $this->fakeThemealdb();

        $stats = app(MealDbSyncService::class)->sync('Japanese');

        $this->assertSame(1, $stats['inserted']);
        $this->assertSame(0, $stats['deactivated']);
        $this->assertSame(1, Recipe::count());
        $this->assertSame('Japanese', Recipe::value('area'));
    }

    public function test_sync_command_outputs_stats(): void
    {
        $this->fakeThemealdb();

        $this->artisan('app:mealdb:sync')
            ->expectsOutputToContain('Synced: 2 inserted, 0 updated, 0 skipped, 0 deactivated, 0 failed.')
            ->assertExitCode(0);
    }

    public function test_sync_command_fails_when_api_unavailable(): void
    {
        Http::fake(['*/list.php*' => Http::response([], 500)]);

        $this->artisan('app:mealdb:sync')
            ->expectsOutputToContain('Sync failed:')
            ->assertExitCode(1);
    }

    public function test_sync_endpoint_requires_token(): void
    {
        $this->postJson('/api/sync/mealdb')->assertStatus(403);
    }

    public function test_sync_endpoint_rejects_wrong_token(): void
    {
        $this->withHeader('X-Sync-Token', 'wrong')->postJson('/api/sync/mealdb')->assertStatus(403);
    }

    public function test_sync_endpoint_starts_async_with_valid_token(): void
    {
        $this->fakeThemealdb();
        config(['services.mealdb_sync.token' => 'secret']);

        $this->withHeader('X-Sync-Token', 'secret')
            ->postJson('/api/sync/mealdb')
            ->assertStatus(202)
            ->assertJsonFragment(['message' => 'MealDB sync started.']);

        // In tests the kernel terminates synchronously, so the afterResponse
        // closure has already run and completed the sync.
        $this->assertSame(2, Recipe::count());
    }

    public function test_sync_endpoint_wait_returns_stats(): void
    {
        $this->fakeThemealdb();
        config(['services.mealdb_sync.token' => 'secret']);

        $this->withHeader('X-Sync-Token', 'secret')
            ->postJson('/api/sync/mealdb?wait=1')
            ->assertStatus(200)
            ->assertJsonFragment([
                'inserted' => 2,
                'updated' => 0,
                'skipped' => 0,
                'deactivated' => 0,
                'failed' => 0,
            ]);

        $this->assertSame(2, Recipe::count());
    }

    public function test_sync_endpoint_returns_409_when_already_running(): void
    {
        $this->fakeThemealdb();
        config(['services.mealdb_sync.token' => 'secret']);
        Cache::lock('mealdb_sync', 10)->get();

        $this->withHeader('X-Sync-Token', 'secret')
            ->postJson('/api/sync/mealdb')
            ->assertStatus(409);
    }

    public function test_sync_endpoint_is_throttled(): void
    {
        $this->fakeThemealdb();
        config(['services.mealdb_sync.token' => 'secret']);

        $request = fn () => $this->withHeader('X-Sync-Token', 'secret')->postJson('/api/sync/mealdb?wait=1');

        $request();
        $request();
        $request()->assertStatus(429);
    }
}
