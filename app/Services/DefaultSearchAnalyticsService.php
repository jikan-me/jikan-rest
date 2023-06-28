<?php

namespace App\Services;
use App\SearchMetric;
use App\Contracts\SearchAnalyticsService;
use Illuminate\Support\Collection;


/**
 * The default search analytics service implementation, which saves the stats to the database and indexes it in Typesense.
 * 
 * By indexing search terms in Typesense we can use it to provide search suggestions of popular searches.
 * @package App\Services
 */
final class DefaultSearchAnalyticsService implements SearchAnalyticsService
{
    public function logSearch(string $searchTerm, int $hitsCount, Collection $hits, string $indexName): void
    {
        /**
         * @var \Laravel\Scout\Builder $existingMetrics
         */
        $existingMetrics = SearchMetric::search($searchTerm);

        $hitList = $hits->pluck("id")->values()->map(fn($x) => (int)$x)->all();

        if ($existingMetrics->count() > 0) {
            $metric = $existingMetrics->first();
            $metric->hits = $hitList;
            $metric->hits_count = $hitsCount;
            $metric->request_count = $metric->request_count + 1;
            $metric->index_name = $indexName;
            $metric->save();
        } else {
            SearchMetric::create([
                "search_term" => $searchTerm,
                "request_count" => 1,
                "hits" => $hitList,
                "hits_count" => $hitsCount,
                "index_name" => $indexName
            ]);
        }
    }
}
