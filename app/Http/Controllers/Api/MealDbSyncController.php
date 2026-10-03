<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MealDbSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MealDbSyncController extends Controller
{
    private const LOCK_KEY = 'mealdb_sync';
    private const LOCK_TTL_SECONDS = 1800;

    public function store(Request $request, MealDbSyncService $service): JsonResponse
    {
        $lock = Cache::lock(self::LOCK_KEY, self::LOCK_TTL_SECONDS);

        if (!$lock->get()) {
            return response()->json(['message' => 'MealDB sync already running.'], 409);
        }

        if ($request->boolean('wait')) {
            return $this->syncBlocking($service, $lock);
        }

        dispatch(fn () => $this->syncBackground($service, $lock))->afterResponse();

        return response()->json(['message' => 'MealDB sync started.'], 202);
    }

    private function syncBlocking(MealDbSyncService $service, $lock): JsonResponse
    {
        try {
            $stats = $service->sync();
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'MealDB sync failed.'], 500);
        } finally {
            $lock->release();
        }

        return response()->json(array_merge(
            ['message' => 'MealDB sync completed.'],
            $stats
        ));
    }

    private function syncBackground(MealDbSyncService $service, $lock): void
    {
        try {
            $stats = $service->sync();
            Log::info('MealDB sync completed.', $stats);
        } catch (\Throwable $e) {
            Log::error('MealDB sync failed.', ['error' => $e->getMessage()]);
        } finally {
            $lock->release();
        }
    }
}
