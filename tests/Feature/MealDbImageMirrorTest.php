<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Repositories\DatabaseRecipeRepository;
use App\Services\MealDbSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MealDbImageMirrorTest extends TestCase
{
    use RefreshDatabase;

    private array $meals = [];

    /**
     * @var array<string, int>
     */
    private array $imageRequestCounts = [];

    /**
     * When set, this image URL fails instead of returning image bytes.
     */
    private ?string $failingImageUrl = null;

    /**
     * Content type served for image URLs (overridden to non-image to test
     * the fallback path).
     */
    private string $imageContentType = 'image/jpeg';

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
                'strTags' => null,
                'strYoutube' => null,
                'strSource' => null,
                'strIngredient1' => 'chicken',
                'strMeasure1' => '2 lbs',
            ],
            '52819' => [
                'idMeal' => '52819',
                'strMeal' => 'Tarte Tatin',
                'strCategory' => 'Dessert',
                'strArea' => 'French',
                'strInstructions' => 'Test dessert instructions',
                'strMealThumb' => 'https://www.themealdb.com/images/media/meals/ttsspp152441.png',
                'strTags' => null,
                'strYoutube' => null,
                'strSource' => null,
                'strIngredient1' => 'flour',
                'strMeasure1' => '200g',
            ],
        ];

        config([
            'services.themealdb.mirror_images' => true,
            'services.themealdb.image_disk' => 'public',
            'services.themealdb.image_pool_size' => 2,
        ]);
        // Storage::fake() does not copy the disk's "url" config — pass it so
        // generated URLs match production (absolute, APP_URL-based).
        Storage::fake('public', ['url' => 'http://localhost/storage']);
    }

    private function fakeThemealdb(): void
    {
        Http::fake(function (Request $request) {
            $url = $request->url();

            if (str_contains($url, '/images/media/meals/')) {
                $this->imageRequestCounts[$url] = ($this->imageRequestCounts[$url] ?? 0) + 1;

                if ($url === $this->failingImageUrl) {
                    return Http::response('Not found', 404);
                }

                return Http::response('fake-image-bytes', 200, ['Content-Type' => $this->imageContentType]);
            }

            if (str_contains($url, '/filter.php')) {
                $area = $request['a'] ?? null;

                $meals = collect($this->meals)
                    ->filter(fn ($meal) => $meal['strArea'] === $area)
                    ->map(fn ($meal) => [
                        'idMeal' => $meal['idMeal'],
                        'strMeal' => $meal['strMeal'],
                        'strMealThumb' => $meal['strMealThumb'],
                    ])
                    ->values()
                    ->all();

                return Http::response(['meals' => $meals ?: null]);
            }

            if (str_contains($url, '/search.php')) {
                $letter = strtolower($request['f'] ?? '');

                $meals = collect($this->meals)
                    ->filter(fn ($meal) => strtolower(substr($meal['strMeal'], 0, 1)) === $letter)
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

    public function test_sync_downloads_and_stores_images_when_mirroring_enabled(): void
    {
        $this->fakeThemealdb();

        $stats = app(MealDbSyncService::class)->sync();

        $this->assertSame(2, $stats['images_mirrored']);
        $this->assertSame(0, $stats['images_failed']);

        $disk = Storage::disk('public');
        $disk->assertExists('recipe-images/52772.jpg');
        $disk->assertExists('recipe-images/52819.png');

        $recipe = Recipe::where('external_id', '52772')->first();
        $this->assertSame('https://www.themealdb.com/images/media/meals/wvpsxx1468256321.jpg', $recipe->source_thumb_url);
        $this->assertSame('http://localhost/storage/recipe-images/52772.jpg', $recipe->thumb_url);

        // Reads served from the DB display the stored copy.
        $details = app(DatabaseRecipeRepository::class)->getRecipeDetails('52772');
        $this->assertSame('http://localhost/storage/recipe-images/52772.jpg', $details['strMealThumb']);
    }

    public function test_sync_keeps_hotlinking_when_mirroring_disabled(): void
    {
        config(['services.themealdb.mirror_images' => false]);
        $this->fakeThemealdb();

        $stats = app(MealDbSyncService::class)->sync();

        $this->assertSame(0, $stats['images_mirrored']);
        $this->assertSame(0, $stats['images_failed']);
        $this->assertSame([], Storage::disk('public')->allFiles());

        $recipe = Recipe::where('external_id', '52772')->first();
        $this->assertSame('https://www.themealdb.com/images/media/meals/wvpsxx1468256321.jpg', $recipe->thumb_url);
        $this->assertSame($recipe->thumb_url, $recipe->source_thumb_url);
    }

    public function test_failed_downloads_fall_back_to_hotlinking(): void
    {
        $this->failingImageUrl = 'https://www.themealdb.com/images/media/meals/wvpsxx1468256321.jpg';
        $this->fakeThemealdb();

        $stats = app(MealDbSyncService::class)->sync();

        $this->assertSame(1, $stats['images_mirrored']);
        $this->assertSame(1, $stats['images_failed']);

        $recipe = Recipe::where('external_id', '52772')->first();
        $this->assertSame($this->failingImageUrl, $recipe->thumb_url);
        $this->assertSame($recipe->thumb_url, $recipe->source_thumb_url);
        Storage::disk('public')->assertMissing('recipe-images/52772.jpg');
    }

    public function test_non_image_response_falls_back_to_hotlinking(): void
    {
        $this->imageContentType = 'text/html';
        $this->fakeThemealdb();

        $stats = app(MealDbSyncService::class)->sync();

        $this->assertSame(0, $stats['images_mirrored']);
        $this->assertSame(2, $stats['images_failed']);
        $this->assertSame(
            'https://www.themealdb.com/images/media/meals/wvpsxx1468256321.jpg',
            Recipe::where('external_id', '52772')->value('thumb_url')
        );
    }

    public function test_second_sync_skips_existing_downloads(): void
    {
        $this->fakeThemealdb();
        $service = app(MealDbSyncService::class);

        $service->sync();
        $stats = $service->sync();

        $this->assertSame(0, $stats['images_mirrored']);
        $this->assertSame(0, $stats['images_failed']);
        // Both thumbs were downloaded exactly once, on the first sync.
        $this->assertSame([1, 1], array_values($this->imageRequestCounts));
        $this->assertSame(
            'http://localhost/storage/recipe-images/52772.jpg',
            Recipe::where('external_id', '52772')->value('thumb_url')
        );
    }

    public function test_source_url_change_triggers_redownload(): void
    {
        $this->fakeThemealdb();
        $service = app(MealDbSyncService::class);

        $service->sync();

        $newUrl = 'https://www.themealdb.com/images/media/meals/updated.jpg';
        $this->meals['52772']['strMealThumb'] = $newUrl;

        $stats = $service->sync();

        $this->assertSame(1, $stats['images_mirrored']);
        Storage::disk('public')->assertExists('recipe-images/52772.jpg');

        $recipe = Recipe::where('external_id', '52772')->first();
        $this->assertSame($newUrl, $recipe->source_thumb_url);
        $this->assertSame('http://localhost/storage/recipe-images/52772.jpg', $recipe->thumb_url);
    }

    public function test_partial_area_sync_mirrors_its_images(): void
    {
        $this->fakeThemealdb();

        $stats = app(MealDbSyncService::class)->sync('Japanese');

        $this->assertSame(1, $stats['images_mirrored']);
        $this->assertSame(1, Recipe::count());
        Storage::disk('public')->assertExists('recipe-images/52772.jpg');
    }
}
