<?php

namespace App\Services;

use App\Models\Recipe;
use App\Support\Backfills\BackfillSavedRecipeFk;
use Illuminate\Support\Facades\DB;

class MealDbSyncService
{
    private const LETTERS = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
        'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    public function __construct(private MealDbClient $client) {}

    /**
     * Sync TheMealDB recipes into the local database.
     *
     * @param  string|null  $area  Limit enumeration to one area (partial sync).
     * @return array{inserted: int, updated: int, skipped: int, deactivated: int, failed: int, relinked: int}
     */
    public function sync(?string $area = null): array
    {
        $stats = ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'deactivated' => 0, 'failed' => 0, 'relinked' => 0];

        if ($area !== null) {
            $this->syncByArea($area, $stats);

            $stats['relinked'] = BackfillSavedRecipeFk::run();

            return $stats;
        }

        $idMeals = $this->syncByLetter($stats);
        if (empty($idMeals)) {
            throw new \RuntimeException('TheMealDB enumeration returned no meals (search endpoints failed).');
        }

        $stats['deactivated'] = $this->reconcileUnreachable($idMeals, $stats);
        $stats['relinked'] = BackfillSavedRecipeFk::run();

        return $stats;
    }

    /**
     * Full sync: search by first letter (uncapped on premium keys, returns
     * full records, so no per-meal lookups are needed). 36 sequential
     * requests total. Any letter failure aborts the sync before
     * deactivation so an outage can never deactivate the whole catalogue.
     */
    private function syncByLetter(array &$stats): array
    {
        $idMeals = [];

        foreach (self::LETTERS as $letter) {
            foreach ($this->client->mealsByLetter($letter) as $payload) {
                $this->upsertMeal($payload, $stats);
                $idMeal = $payload['idMeal'] ?? null;

                if (is_string($idMeal) && $idMeal !== '') {
                    $idMeals[$idMeal] = true;
                }
            }
        }

        return array_keys($idMeals);
    }

    private function syncByArea(string $area, array &$stats): void
    {
        $summaries = $this->client->mealsByArea($area);
        if (empty($summaries)) {
            throw new \RuntimeException("No meals found for area '{$area}'.");
        }

        $meals = $this->client->lookupMany(array_column($summaries, 'idMeal'));

        foreach ($meals as $payload) {
            if ($payload === null) {
                $stats['failed']++;

                continue;
            }

            $this->upsertMeal($payload, $stats);
        }
    }

    private function upsertMeal(array $payload, array &$stats): void
    {
        $row = $this->mapMeal($payload);
        if ($row === null) {
            $stats['failed']++;

            return;
        }

        [$record, $ingredients] = $row;

        $existing = Recipe::where('external_id', $record['external_id'])->first();

        if ($existing === null) {
            $recipe = Recipe::create($record + ['last_synced_at' => now()]);
            $recipe->ingredients()->createMany($ingredients);
            $stats['inserted']++;
        } elseif ($existing->data_hash !== $record['data_hash']) {
            DB::transaction(function () use ($existing, $record, $ingredients) {
                $existing->update($record + ['last_synced_at' => now(), 'is_active' => true]);
                $existing->ingredients()->delete();
                $existing->ingredients()->createMany($ingredients);
            });
            $stats['updated']++;
        } else {
            $existing->update(['last_synced_at' => now(), 'is_active' => true]);
            $stats['skipped']++;
        }
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
     * Active recipes the letter enumeration could not reach (unusual title
     * characters) are reconciled by direct lookup: still alive means
     * upsert (and re-activate), definitively gone means deactivate. A
     * lookup failure aborts the sync rather than deactivating anything.
     */
    private function reconcileUnreachable(array $syncedIdMeals, array &$stats): int
    {
        $candidates = Recipe::query()
            ->where('source', 'themealdb')
            ->whereNotIn('external_id', $syncedIdMeals)
            ->pluck('external_id');

        $deactivated = 0;

        foreach ($candidates as $externalId) {
            $payload = $this->client->lookupStrict($externalId);

            if ($payload === null) {
                $deactivated += Recipe::where('external_id', $externalId)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);

                continue;
            }

            $this->upsertMeal($payload, $stats);
        }

        return $deactivated;
    }
}
