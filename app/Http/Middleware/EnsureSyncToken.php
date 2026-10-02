<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSyncToken
{
    public function handle(Request $request, Closure $next)
    {
        $expected = config('services.mealdb_sync.token');

        if (! is_string($expected) || $expected === '' || ! is_string($request->header('X-Sync-Token'))
            || ! hash_equals($expected, $request->header('X-Sync-Token'))) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return $next($request);
    }
}
