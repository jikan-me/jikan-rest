<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface SearchAnalyticsService
{
    public function logSearch(string $searchTerm, int $hitsCount, Collection $hits, string $indexName): void;
}
