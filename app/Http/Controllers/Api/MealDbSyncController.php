<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MealDbSyncService;
use Illuminate\Http\JsonResponse;

class MealDbSyncController extends Controller
{
    public function store(MealDbSyncService $service): JsonResponse
    {
        try {
            $stats = $service->sync();
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'MealDB sync failed.',
            ], 500);
        }

        return response()->json(array_merge(
            ['message' => 'MealDB sync completed.'],
            $stats
        ));
    }
}
