<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Detects the silent-empty case where the search engine matched documents but
 * Mongo hydration returned fewer models than the current page should contain.
 *
 * This is diagnostic only: it never changes the response.
 */
final class SearchHydrationGuard
{
    public static function check(
        int $engineHits,
        int $hydrated,
        int $page,
        int $perPage,
        ?string $query,
        ?string $route
    ): void {
        if ($engineHits <= 0 || $perPage <= 0) {
            return;
        }

        $offset = max(0, ($page - 1) * $perPage);
        $expected = max(0, min($perPage, $engineHits - $offset));

        if ($expected > 0 && $hydrated < $expected) {
            Log::warning('search.index_mismatch', [
                'engine_hits' => $engineHits,
                'hydrated' => $hydrated,
                'expected' => $expected,
                'page' => $page,
                'per_page' => $perPage,
                'query' => $query,
                'route' => $route,
            ]);
        }
    }
}
