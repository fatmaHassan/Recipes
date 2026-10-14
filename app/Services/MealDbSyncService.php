<?php

namespace App\Services;

use App\Models\Recipe;
use App\Support\Backfills\BackfillSavedRecipeFk;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MealDbSyncService
{
    private const LETTERS = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
        'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    /** Directory (on the image disk) holding the mirrored meal thumbs. */
    private const IMAGE_DIR = 'recipe-images';

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    private bool $mirrorImages;

    private string $imageDisk;

    private int $imagePoolSize;

    private int $imageBatchSleepMs;

    /** @var array<string, array{path: string, source: string}> */
    private array $pendingImages = [];

    public function __construct(private MealDbClient $client)
    {
        $this->mirrorImages = (bool) config('services.themealdb.mirror_images', false);
        $this->imageDisk = (string) config('services.themealdb.image_disk', 'public');
        $this->imagePoolSize = max(1, (int) config('services.themealdb.image_pool_size', 10));
        $this->imageBatchSleepMs = max(0, (int) config('services.themealdb.batch_sleep_ms', 0));
    }

    /**
     * Sync TheMealDB recipes into the local database.
     *
     * @param  string|null  $area  Limit enumeration to one area (partial sync).
     * @return array{inserted: int, updated: int, skipped: int, deactivated: int, failed: int, relinked: int, images_mirrored: int, images_failed: int}
     */
    public function sync(?string $area = null): array
    {
        $stats = ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'deactivated' => 0, 'failed' => 0, 'relinked' => 0,
            'images_mirrored' => 0, 'images_failed' => 0];

        if ($area !== null) {
            $this->syncByArea($area, $stats);

            $this->flushPendingImages($stats);

            $stats['relinked'] = BackfillSavedRecipeFk::run();

            return $stats;
        }

        $idMeals = $this->syncByLetter($stats);
        if (empty($idMeals)) {
            throw new \RuntimeException('TheMealDB enumeration returned no meals (search endpoints failed).');
        }

        $stats['deactivated'] = $this->reconcileUnreachable($idMeals, $stats);
        $this->flushPendingImages($stats);
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

        // Mirror step decides what thumb_url displays: the stored copy when
        // mirroring succeeds, the CDN URL as fallback otherwise.
        $record['thumb_url'] = $this->resolveThumbUrl($record, $existing);

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
            // Unchanged meal: still repair the thumb URLs if the mirror state
            // drifted (mirror toggled off/on, disk changed, APP_URL moved).
            $attributes = ['last_synced_at' => now(), 'is_active' => true];
            foreach (['thumb_url', 'source_thumb_url'] as $column) {
                if ($existing->{$column} !== $record[$column]) {
                    $attributes[$column] = $record[$column];
                }
            }
            $existing->update($attributes);
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

        $thumbUrl = $this->nullIfEmpty($meal['strMealThumb'] ?? null);

        $record = [
            'external_id' => $idMeal,
            'source' => 'themealdb',
            'title' => $title,
            'category' => $this->nullIfEmpty($meal['strCategory'] ?? null),
            'area' => $this->nullIfEmpty($meal['strArea'] ?? null),
            'instructions' => $this->nullIfEmpty($meal['strInstructions'] ?? null),
            'thumb_url' => $thumbUrl,
            'source_thumb_url' => $thumbUrl,
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

    /**
     * Decide the thumb URL to display for a meal. When mirroring is enabled
     * this is the stored copy's URL; whenever that can't be confirmed (mirror
     * off, download still pending, previous download failed) the CDN URL is
     * kept as fallback so images never 404 because of the mirror.
     */
    private function resolveThumbUrl(array $record, ?Recipe $existing): ?string
    {
        $source = $record['source_thumb_url'] ?? $record['thumb_url'];

        if (! $this->mirrorImages || $source === null) {
            return $source;
        }

        $path = $this->imagePath($record['external_id'], $source);
        $url = $this->imageUrl($path);

        // The stored copy is current: same source URL, thumb_url already
        // points at it, and the file survived on disk. No download needed.
        if ($existing !== null
            && $existing->source_thumb_url === $source
            && $existing->thumb_url === $url
            && Storage::disk($this->imageDisk)->exists($path)
        ) {
            return $url;
        }

        // Pessimistic: keep hotlinking until the download is confirmed, then
        // flushPendingImages() patches thumb_url to the stored copy.
        $this->pendingImages[$record['external_id']] = [
            'path' => $path,
            'source' => $source,
        ];

        return $record['thumb_url'];
    }

    /**
     * Download the images queued during the sync, in concurrent batches like
     * the lookups, and point thumb_url at the stored copies. A failure keeps
     * the CDN URL hotlinked (it was written during the upsert), so image
     * problems can never fail or blank out the sync.
     */
    private function flushPendingImages(array &$stats): void
    {
        if ($this->pendingImages === []) {
            return;
        }

        $disk = Storage::disk($this->imageDisk);

        foreach (array_chunk($this->pendingImages, $this->imagePoolSize, true) as $index => $batch) {
            if ($index > 0 && $this->imageBatchSleepMs > 0) {
                usleep($this->imageBatchSleepMs * 1000);
            }

            $responses = Http::pool(fn ($pool) => collect($batch)->each(
                fn ($target, $externalId) => $pool->as($externalId)
                    ->retry(2, 300)
                    ->timeout(30)
                    ->get($target['source'])
            ));

            foreach ($responses as $externalId => $response) {
                $target = $batch[$externalId];

                // Failed pool requests come back as RequestException/ConnectionException, not Response
                if (! $response instanceof Response || ! $response->successful() || ! $this->isImageResponse($response)) {
                    Log::warning('TheMealDB image mirror failed', [
                        'external_id' => $externalId,
                        'source' => $target['source'],
                    ]);
                    $stats['images_failed']++;

                    continue;
                }

                $disk->put($target['path'], $response->body());
                Recipe::where('external_id', $externalId)
                    ->update(['thumb_url' => $this->imageUrl($target['path'])]);
                $stats['images_mirrored']++;
            }
        }

        $this->pendingImages = [];
    }

    /**
     * One file per meal id, keyed deterministically so re-syncs can skip
     * existing downloads; extension is derived from the source URL.
     */
    private function imagePath(string $externalId, string $sourceUrl): string
    {
        $extension = strtolower(pathinfo(parse_url($sourceUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));

        if (! in_array($extension, self::IMAGE_EXTENSIONS, true)) {
            $extension = 'jpg';
        }

        return self::IMAGE_DIR."/{$externalId}.{$extension}";
    }

    private function imageUrl(string $path): string
    {
        return (string) Storage::disk($this->imageDisk)->url($path);
    }

    private function isImageResponse(Response $response): bool
    {
        return str_starts_with(strtolower($response->header('Content-Type')), 'image');
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
