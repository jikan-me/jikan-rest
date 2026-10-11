<?php

namespace App\Services;

use App\Contracts\SearchAnalyticsService;
use App\SearchMetric;
use Illuminate\Support\Collection;
use Typesense\Documents;
use Typesense\Exceptions\TypesenseClientError;

/**
 * The default search analytics service implementation, which saves the stats to the database and indexes it in Typesense.
 *
 * By indexing search terms in Typesense we can use it to provide search suggestions of popular searches.
 */
final class DefaultSearchAnalyticsService implements SearchAnalyticsService
{
    /**
     * @param  Collection<int, mixed>  $hits
     */
    public function logSearch(string $searchTerm, int $hitsCount, Collection $hits, string $indexName): void
    {
        try {
            /** @var Collection<int, SearchMetric> $existingMetrics */
            $existingMetrics = SearchMetric::search($searchTerm, function (Documents $documents, string $query, array $options) {
                if (strlen($query) <= 3) {
                    $options['prioritize_token_position'] = 'true';
                }

                return $documents->search($options);
            })->take(1)->get();
        } catch (TypesenseClientError $e) {
            // On the very first search against a fresh Typesense server the
            // metrics collection does not exist yet — treat it as "no existing
            // metrics"; SearchMetric::create() below bootstraps the collection.
            $existingMetrics = collect();
        }

        $hitList = $hits->pluck('id')->values()->map(fn ($x) => (int) $x)->all();

        if ($existingMetrics->count() > 0) {
            /**
             * @var SearchMetric $metric
             */
            $metric = $existingMetrics->first();
            $metric->hits = $hitList;
            $metric->hits_count = $hitsCount;
            $metric->request_count = $metric->request_count + 1;
            $metric->index_name = $indexName;
            $metric->save();
        } else {
            SearchMetric::create([
                'search_term' => $searchTerm,
                'request_count' => 1,
                'hits' => $hitList,
                'hits_count' => $hitsCount,
                'index_name' => $indexName,
            ]);
        }
    }
}
