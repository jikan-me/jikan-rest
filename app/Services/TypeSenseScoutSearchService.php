<?php

namespace App\Services;

use App\Contracts\Repository;
use App\Contracts\SearchAnalyticsService;
use App\JikanApiSearchableModel;
use App\Support\JikanConfig;
use App\Support\SearchEngineFailure;
use Illuminate\Support\Str;
use Laravel\Scout\Builder;
use Typesense\Documents;

class TypeSenseScoutSearchService implements ScoutSearchService
{
    private int $maxItemsPerPage;

    private JikanConfig $jikanConfig;

    public function __construct(private readonly Repository $repository,
        JikanConfig $config,
        private readonly TypesenseCollectionDescriptor $collectionDescriptor,
        private readonly SearchAnalyticsService $searchAnalytics)
    {
        $this->maxItemsPerPage = (int) $config->maxResultsPerPage();
        $this->jikanConfig = $config;
        if ($this->maxItemsPerPage > 250) {
            $this->maxItemsPerPage = 250;
        }
    }

    /**
     * Executes a search operation via Laravel Scout on the provided model class.
     */
    public function search(string $q, ?string $orderByField = null,
        bool $sortDirectionDescending = false): Builder
    {
        $model = $this->repository->createEntity();
        $suppressExactId = $model instanceof JikanApiSearchableModel
            && is_numeric($q)
            && $this->hasExactTitle($model, $q);

        return $this->repository->search($q, $this->middleware($orderByField, $sortDirectionDescending, $suppressExactId));
    }

    private function hasExactTitle(JikanApiSearchableModel $model, string $q): bool
    {
        $titleAttribute = $model->getTitleAttributeName();

        // Producers store titles as a string/array column; numeric title matches
        // are not meaningful there.
        if ($titleAttribute === 'titles') {
            return false;
        }

        try {
            return $model->newQuery()->where($titleAttribute, $q)->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    private function middleware(?string $orderByField = null, bool $sortDirectionDescending = false, bool $suppressExactId = false): \Closure
    {
        return function (Documents $documents, string $query, array $options) use ($orderByField, $sortDirectionDescending, $suppressExactId) {
            $options['exhaustive_search'] = $this->jikanConfig->exhaustiveSearch();
            $options['search_cutoff_ms'] = $this->jikanConfig->searchCutOffMs();
            // this will be ignored together with exhaustive_search set to "true"
            $options['drop_tokens_threshold'] = $this->jikanConfig->dropTokensThreshold();
            $options['typo_tokens_threshold'] = $this->jikanConfig->typoTokensThreshold();
            $options['enable_highlight_v1'] = 'false';
            $options['infix'] = 'fallback';
            $options['prioritize_exact_match'] = 'true';
            // prevent `Could not parse the filter query: unbalanced `&&` operands.` error
            // this adds support for typesense v0.24.1
            if (array_key_exists('filter_by', $options) && ($options['filter_by'] === ' && ' || $options['filter_by'] === '&&')) {
                unset($options['filter_by']);
            }

            if (array_key_exists('per_page', $options) && $options['per_page'] > 250) {
                $options['per_page'] = min($this->maxItemsPerPage, 250);
            }

            $modelInstance = $this->repository->createEntity();
            $modelTitleAttributeName = 'title';

            if ($modelInstance instanceof JikanApiSearchableModel) {
                $options = $this->setQueryByWeights($options, $modelInstance);
                $options = $this->setSortOrder($options, $modelInstance);
                $options = $this->overrideSortingOrder($options, $modelInstance, $orderByField, $sortDirectionDescending);
                $modelTitleAttributeName = $modelInstance->getTitleAttributeName();
            }
            $options = $this->adaptToShortQueries($query, $modelTitleAttributeName, $options);

            if ($suppressExactId) {
                $options = $this->withoutExactIdField($options);
            }

            try {
                $results = $documents->search($options);
            } catch (\Throwable $e) {
                // Missing/unreachable collection (e.g. not yet imported): degrade
                // to an empty result instead of 500, matching the graceful
                // empty-index behaviour of other searches. Auth/config errors
                // (401/403, bad API key) and anything else are reported and rethrown
                // so a misconfigured deployment is not silently serving nothing.
                report($e);
                if (!SearchEngineFailure::isDegradable($e)) {
                    throw $e;
                }
                $results = ['hits' => [], 'found' => 0, 'out_of' => 0, 'page' => 1,
                    'request_params' => ['q' => $query, 'per_page' => $options['per_page'] ?? 250]];
            }
            try {
                $this->recordSearchTelemetry($query, $results);
            } catch (\Throwable $e) {
                report($e);
            }

            return $results;
        };
    }

    /**
     * Exact-title queries should win over exact numeric IDs. Remove the ID
     * search field for those queries so title relevance and popularity decide.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function withoutExactIdField(array $options): array
    {
        if (empty($options['query_by'])) {
            return $options;
        }

        $fields = explode(',', (string) $options['query_by']);
        $index = array_search('mal_id_text', $fields, true);
        if ($index === false) {
            return $options;
        }

        array_splice($fields, $index, 1);
        $options['query_by'] = implode(',', $fields);

        if (!empty($options['query_by_weights'])) {
            $weights = explode(',', (string) $options['query_by_weights']);
            array_splice($weights, $index, 1);
            $options['query_by_weights'] = implode(',', $weights);
        }

        return $options;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function adaptToShortQueries(string $query, string $modelTitleAttributeName, array $options): array
    {
        if (strlen($query) <= 3) {
            $options['num_typos'] = 0;
            $options['typo_tokens_threshold'] = 0;
            $options['drop_tokens_threshold'] = 0;
            $options['exhaustive_search'] = 'false';
            $options['infix'] = 'off';
            $options['prioritize_token_position'] = 'true';

            if (array_key_exists('sort_by', $options)) {
                if (Str::startsWith($options['sort_by'], '_text_match')) {
                    $options['sort_by'] = '_text_match:desc,mal_id:asc';
                } else {
                    // move text_match to the beginning if there is an orderby parameter set.
                    $options['sort_by'] = Str::replace('(buckets:'.$this->jikanConfig->textMatchBuckets().')', '', $options['sort_by']);
                    $parts = collect(explode(',', $options['sort_by']));
                    $last = $parts->pop();
                    $parts = $parts->prepend($last);
                    $options['sort_by'] = $parts->implode(',');
                }
            } else {
                $options['sort_by'] = '_text_match:desc,mal_id:asc';
            }
        }

        return $options;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function setQueryByWeights(array $options, JikanApiSearchableModel $modelInstance): array
    {
        $queryByWeights = $modelInstance->getTypeSenseQueryByWeights();
        if (!is_null($queryByWeights)) {
            $options['query_by_weights'] = $queryByWeights;
        }

        return $options;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function setSortOrder(array $options, JikanApiSearchableModel $modelInstance): array
    {
        $sortByFields = $modelInstance->getSearchIndexSortBy();
        if (!is_null($sortByFields)) {
            $sortBy = '';
            foreach ($sortByFields as $f) {
                $sortBy .= $f['field'].':'.$f['direction'];
                $sortBy .= ',';
            }
            $sortBy = rtrim($sortBy, ',');
            $options['sort_by'] = $sortBy;
        } elseif (is_null($sortByFields) && !array_key_exists('sort_by', $options)) {
            $options['sort_by'] = '_text_match:desc';
        }

        return $options;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function overrideSortingOrder(array $options, JikanApiSearchableModel $modelInstance, ?string $orderByField, bool $sortDirectionDescending): array
    {
        $modelAttrNames = $this->collectionDescriptor->getSearchableAttributes($modelInstance);

        // fixme: this shouldn't be here, but it's a quick fix for the time being
        if ($orderByField === 'aired.from') {
            $orderByField = 'start_date';
        }

        if ($orderByField === 'aired.to') {
            $orderByField = 'end_date';
        }

        if ($orderByField === 'published.from') {
            $orderByField = 'start_date';
        }

        if ($orderByField === 'published.to') {
            $orderByField = 'end_date';
        }
        // fixme end

        // override ordering field
        if (!is_null($orderByField) && in_array($orderByField, $modelAttrNames)) {
            $options['sort_by'] = "$orderByField:".($sortDirectionDescending ? 'desc' : 'asc').',_text_match(buckets:'.$this->jikanConfig->textMatchBuckets().'):desc';
        }

        // override overall sorting direction
        if (is_null($orderByField) && $sortDirectionDescending && array_key_exists('sort_by', $options) && Str::contains($options['sort_by'], 'asc')) {
            $options['sort_by'] = Str::replace('asc', 'desc', $options['sort_by']);
        }

        return $options;
    }

    /**
     * @param  array<string, mixed>  $typesenseApiResponse
     */
    private function recordSearchTelemetry(string $query, array $typesenseApiResponse): void
    {
        $hits = collect($typesenseApiResponse['hits']);
        $this->searchAnalytics->logSearch(
            $query,
            $typesenseApiResponse['found'],
            $hits->pluck('document')->values(),
            $typesenseApiResponse['request_params']['collection_name']
        );
    }
}
