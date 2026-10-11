<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface SearchAnalyticsService
{
    /**
     * @param  Collection<int, mixed>  $hits
     */
    public function logSearch(string $searchTerm, int $hitsCount, Collection $hits, string $indexName): void;
}
