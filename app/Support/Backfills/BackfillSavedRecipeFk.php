<?php

namespace App\Support\Backfills;

use App\Models\Recipe;
use App\Models\SavedRecipe;

class BackfillSavedRecipeFk
{
    public static function run(): int
    {
        $linked = 0;

        SavedRecipe::query()
            ->whereNull('recipe_internal_id')
            ->orderBy('id')
            ->chunkById(500, function ($savedRecipes) use (&$linked) {
                $externalIds = $savedRecipes->pluck('recipe_id')->unique()->all();

                $map = Recipe::query()
                    ->whereIn('external_id', $externalIds)
                    ->pluck('id', 'external_id');

                foreach ($savedRecipes as $saved) {
                    $internalId = $map[$saved->recipe_id] ?? null;

                    if ($internalId !== null) {
                        $saved->update(['recipe_internal_id' => $internalId]);
                        $linked++;
                    }
                }
            });

        return $linked;
    }
}
