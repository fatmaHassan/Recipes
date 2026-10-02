<?php

namespace App\Console\Commands;

use App\Services\MealDbSyncService;
use Illuminate\Console\Command;

class MealDbSyncCommand extends Command
{
    protected $signature = 'app:mealdb:sync {--area= : Only sync meals from this area/cuisine}';

    protected $description = 'Sync recipes from TheMealDB into the local database';

    public function handle(MealDbSyncService $service): int
    {
        $area = $this->option('area');

        $this->info($area
            ? "Syncing TheMealDB recipes for area '{$area}'..."
            : 'Syncing all TheMealDB recipes...');

        try {
            $stats = $service->sync($area);
        } catch (\Throwable $e) {
            $this->error('Sync failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("Synced: {$stats['inserted']} inserted, {$stats['updated']} updated, "
            ."{$stats['skipped']} skipped, {$stats['deactivated']} deactivated, {$stats['failed']} failed.");

        return self::SUCCESS;
    }
}
