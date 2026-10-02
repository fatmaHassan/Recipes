<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MealDbClient
{
    private string $baseUrl;

    private string $apiKey;

    private int $poolSize;

    private int $batchSleepMs;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.themealdb.base_url'), '/').'/';
        $this->apiKey = config('services.themealdb.api_key', '1');
        $this->poolSize = max(1, (int) config('services.themealdb.pool_size', 10));
        $this->batchSleepMs = max(0, (int) config('services.themealdb.batch_sleep_ms', 0));
    }

    /**
     * List all areas (cuisines). Returns [] on failure.
     */
    public function areas(): array
    {
        return $this->listValues('a', 'strArea');
    }

    /**
     * List all categories. Returns [] on failure.
     */
    public function categories(): array
    {
        return $this->listValues('c', 'strCategory');
    }

    /**
     * Get meal summaries (idMeal, strMeal, strMealThumb) by area.
     */
    public function mealsByArea(string $area): array
    {
        return $this->filterMeals(['a' => $area]);
    }

    /**
     * Get meal summaries (idMeal, strMeal, strMealThumb) by category.
     */
    public function mealsByCategory(string $category): array
    {
        return $this->filterMeals(['c' => $category]);
    }

    /**
     * Fetch full details for a single meal.
     */
    public function lookup(string $idMeal): ?array
    {
        try {
            $response = Http::retry(3, 500)->timeout(30)
                ->get($this->baseUrl.'lookup.php', [
                    'i' => $idMeal,
                    'apikey' => $this->apiKey,
                ]);

            if ($response->successful()) {
                return $response->json('meals.0');
            }

            Log::warning('TheMealDB lookup failed', ['id' => $idMeal, 'status' => $response->status()]);

            return null;
        } catch (ConnectionException $e) {
            Log::error('TheMealDB lookup connection error', ['id' => $idMeal, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Fetch full details for many meals concurrently, in batches.
     * Returns a map of idMeal => meal payload (null value for failed lookups).
     *
     * @param  string[]  $idMeals
     * @return array<string, ?array>
     */
    public function lookupMany(array $idMeals): array
    {
        $results = [];

        $batches = array_chunk($idMeals, $this->poolSize);

        foreach ($batches as $index => $batch) {
            if ($index > 0 && $this->batchSleepMs > 0) {
                usleep($this->batchSleepMs * 1000);
            }

            $responses = Http::pool(fn ($pool) => collect($batch)->each(
                fn ($id) => $pool->as($id)
                    ->retry(2, 300)
                    ->timeout(30)
                    ->get($this->baseUrl.'lookup.php', [
                        'i' => $id,
                        'apikey' => $this->apiKey,
                    ])
            ));

            foreach ($responses as $id => $response) {
                // Failed pool requests come back as RequestException/ConnectionException, not Response
                if (! $response instanceof Response || ! $response->successful()) {
                    $results[$id] = null;

                    continue;
                }

                $results[$id] = $response->json('meals.0');
            }
        }

        return $results;
    }

    /**
     * Handle list.php endpoints (areas / categories).
     */
    private function listValues(string $param, string $key): array
    {
        try {
            $response = Http::retry(3, 500)->timeout(30)
                ->get($this->baseUrl.'list.php', [$param => 'list', 'apikey' => $this->apiKey]);

            if (! $response->successful()) {
                Log::warning('TheMealDB list request failed', ['param' => $param, 'status' => $response->status()]);

                return [];
            }

            $meals = $response->json('meals') ?? [];

            return array_values(array_filter(array_map(
                fn ($item) => $item[$key] ?? '',
                $meals
            )));
        } catch (ConnectionException $e) {
            Log::error('TheMealDB list connection error', ['param' => $param, 'error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Handle filter.php endpoints. TheMealDB returns null meals for no results.
     */
    private function filterMeals(array $params): array
    {
        try {
            $response = Http::retry(3, 500)->timeout(30)
                ->get($this->baseUrl.'filter.php', $params + ['apikey' => $this->apiKey]);

            if (! $response->successful()) {
                Log::warning('TheMealDB filter request failed', ['params' => $params, 'status' => $response->status()]);

                return [];
            }

            return $response->json('meals') ?? [];
        } catch (ConnectionException $e) {
            Log::error('TheMealDB filter connection error', ['params' => $params, 'error' => $e->getMessage()]);

            return [];
        }
    }
}
