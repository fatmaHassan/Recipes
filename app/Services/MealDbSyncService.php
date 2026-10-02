<?php

namespace App\Services;

use App\Models\Recipe;
use Illuminate\Support\Facades\DB;

class MealDbSyncService
{
    public function __construct(private MealDbClient $client) {}

    /**
     * Sync TheMealDB recipes into the local database.
     *
     * @param  string|null  $area  Limit enumeration to one area (partial sync).
     * @return array{inserted: int, updated: int, skipped: int, deactivated: int, failed: int}
     */
    public function sync(?string $area = null): array
    {
        $stats = ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'deactivated' => 0, 'failed' => 0];

        $idMeals = $this->enumerateMealIds($area);
        if (empty($idMeals)) {
            throw new \RuntimeException('TheMealDB enumeration returned no meals (list/filter endpoints failed).');
        }

        $meals = $this->client->lookupMany($idMeals);

        foreach ($meals as $idMeal => $payload) {
            if ($payload === null) {
                $stats['failed']++;

                continue;
            }

            $row = $this->mapMeal($payload);
            if ($row === null) {
                $stats['failed']++;

                continue;
            }

            [$record, $ingredients] = $row;

            $existing = Recipe::where('external_id', $idMeal)->first();

            if ($existing === null) {
                $recipe = Recipe::create($record + ['last_synced_at' => now()]);
                $recipe->ingredients()->createMany($ingredients);
                $stats['inserted']++;
            } elseif ($existing->data_hash !== $record['data_hash']) {
                DB::transaction(function () use ($existing, $record, $ingredients) {
                    $existing->update($record + ['last_synced_at' => now()]);
                    $existing->ingredients()->delete();
                    $existing->ingredients()->createMany($ingredients);
                });
                $stats['updated']++;
            } else {
                $existing->update(['last_synced_at' => now()]);
                $stats['skipped']++;
            }
        }

        if ($area === null) {
            $stats['deactivated'] = $this->deactivateMissing($idMeals);
        }

        return $stats;
    }

    /**
     * Union of all areas and all categories covers every published meal,
     * including ones with an unknown/empty area.
     *
     * @return string[]
     */
    private function enumerateMealIds(?string $area = null): array
    {
        if ($area !== null) {
            $summaries = $this->client->mealsByArea($area);
            if (empty($summaries)) {
                throw new \RuntimeException("No meals found for area '{$area}'.");
            }

            return array_column($summaries, 'idMeal');
        }

        $ids = [];

        foreach ($this->client->areas() as $a) {
            foreach ($this->client->mealsByArea($a) as $meal) {
                $ids[$meal['idMeal']] = true;
            }
        }

        foreach ($this->client->categories() as $c) {
            foreach ($this->client->mealsByCategory($c) as $meal) {
                $ids[$meal['idMeal']] = true;
            }
        }

        return array_keys($ids);
    }

    /**
     * Map an API payload to [record, ingredients] or null when unusable.
     */
    private function mapMeal(array $meal): ?array
    {
        $idMeal = $meal['idMeal'] ?? null;
        $title = trim($meal['strMeal'] ?? '');

        if (! is_string($idMeal) || $idMeal === '' || $title === '') {
            return null;
        }

        $record = [
            'external_id' => $idMeal,
            'source' => 'themealdb',
            'title' => $title,
            'category' => $this->nullIfEmpty($meal['strCategory'] ?? null),
            'area' => $this->nullIfEmpty($meal['strArea'] ?? null),
            'instructions' => $this->nullIfEmpty($meal['strInstructions'] ?? null),
            'thumb_url' => $this->nullIfEmpty($meal['strMealThumb'] ?? null),
            'tags' => $this->nullIfEmpty($meal['strTags'] ?? null),
            'youtube_url' => $this->nullIfEmpty($meal['strYoutube'] ?? null),
            'source_url' => $this->nullIfEmpty($meal['strSource'] ?? null),
        ];

        $ingredients = [];
        for ($i = 1; $i <= 20; $i++) {
            $name = trim($meal["strIngredient{$i}"] ?? '');
            if ($name === '') {
                continue;
            }

            $ingredients[] = [
                'sort_order' => $i,
                'name' => $name,
                'measure' => $this->nullIfEmpty($meal["strMeasure{$i}"] ?? null),
            ];
        }

        $hashSource = array_merge($record, ['ingredients' => $ingredients]);
        ksort($hashSource);
        $record['data_hash'] = md5(json_encode($hashSource));

        return [$record, $ingredients];
    }

    private function nullIfEmpty(?string $value): ?string
    {
        return ($value ?? '') === '' ? null : $value;
    }

    /**
     * Deactivate themealdb-sourced recipes absent from the API (full sync only).
     */
    private function deactivateMissing(array $syncedIdMeals): int
    {
        return Recipe::where('source', 'themealdb')
            ->where('is_active', true)
            ->whereNotIn('external_id', $syncedIdMeals)
            ->update(['is_active' => false]);
    }
}
