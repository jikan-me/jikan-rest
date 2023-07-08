<?php

namespace App\Services;

use App\Contracts\SearchAnalyticsService;
use Illuminate\Support\Collection;

final class DummySearchAnalyticsService implements SearchAnalyticsService
{
    public function logSearch(string $searchTerm, int $hitsCount, Collection $hits, string $indexName): void
    {
        // noop;
    }
}
