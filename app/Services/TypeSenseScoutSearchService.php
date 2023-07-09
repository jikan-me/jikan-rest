<?php

namespace App\Services;

use App\Contracts\Repository;
use App\JikanApiSearchableModel;
use App\Support\JikanConfig;
use Illuminate\Support\Str;
use Laravel\Scout\Builder;
use Typesense\Documents;
use App\Contracts\SearchAnalyticsService;

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
     * @param string $q
     * @param string|null $orderByField
     * @param bool $sortDirectionDescending
     * @return Builder
     */
    public function search(string $q, ?string $orderByField = null,
                           bool $sortDirectionDescending = false): \Laravel\Scout\Builder
    {
        return $this->repository->search($q, $this->middleware($orderByField, $sortDirectionDescending));
    }

    private function middleware(?string $orderByField = null, bool $sortDirectionDescending = false): \Closure
    {
        return function (Documents $documents, string $query, array $options) use ($orderByField, $sortDirectionDescending) {
            $options['exhaustive_search'] = $this->jikanConfig->exhaustiveSearch();
            $options['search_cutoff_ms'] = $this->jikanConfig->searchCutOffMs();
            // this will be ignored together with exhaustive_search set to "true"
            $options['drop_tokens_threshold'] = $this->jikanConfig->dropTokensThreshold();
            $options['typo_tokens_threshold'] = $this->jikanConfig->typoTokensThreshold();
            $options['enable_highlight_v1'] = 'false';
            $options['infix'] = 'fallback';
            // prevent `Could not parse the filter query: unbalanced `&&` operands.` error
            // this adds support for typesense v0.24.1
            if (array_key_exists('filter_by', $options) && ($options['filter_by'] === ' && ' || $options['filter_by'] === '&&')) {
                unset($options['filter_by']);
            }

            if (array_key_exists('per_page', $options) && $options['per_page'] > 250) {
                $options['per_page'] = min($this->maxItemsPerPage, 250);
            }

            $options = $this->skipTypoCheckingForShortQueries($query, $options);
            $modelInstance = $this->repository->createEntity();

            if ($modelInstance instanceof JikanApiSearchableModel) {
                $options = $this->setQueryByWeights($options, $modelInstance);
                $options = $this->setSortOrder($options, $modelInstance);
                $options = $this->overrideSortingOrder($options, $modelInstance, $orderByField, $sortDirectionDescending);
            }

            $results = $documents->search($options);
            $this->recordSearchTelemetry($query, $results);

            return $results;
        };
    }

    private function skipTypoCheckingForShortQueries(string $query, array $options): array
    {
        if (strlen($query) <= 3) {
            $options['num_typos'] = 0;
            $options['typo_tokens_threshold'] = 0;
            $options['drop_tokens_threshold'] = 0;
            $options['exhaustive_search'] = 'false';
            $options['infix'] = 'off';
            $options['prefix'] = 'false';
        }

        return $options;
    }

    private function setQueryByWeights(array $options, JikanApiSearchableModel $modelInstance): array
    {
        $queryByWeights = $modelInstance->getTypeSenseQueryByWeights();
        if (!is_null($queryByWeights)) {
            $options['query_by_weights'] = $queryByWeights;
        }

        return $options;
    }

    private function setSortOrder(array $options, JikanApiSearchableModel $modelInstance): array
    {
        $sortByFields = $modelInstance->getSearchIndexSortBy();
        if (!is_null($sortByFields)) {
            $sortBy = "";
            foreach ($sortByFields as $f) {
                $sortBy .= $f['field'] . ':' . $f['direction'];
                $sortBy .= ',';
            }
            $sortBy = rtrim($sortBy, ',');
            $options['sort_by'] = $sortBy;
        }

        return $options;
    }

    private function overrideSortingOrder(array $options, JikanApiSearchableModel $modelInstance, ?string $orderByField, bool $sortDirectionDescending): array
    {
        $modelAttrNames = $this->collectionDescriptor->getSearchableAttributes($modelInstance);

        // fixme: this shouldn't be here, but it's a quick fix for the time being
        if ($orderByField === "aired.from") {
            $orderByField = "start_date";
        }

        if ($orderByField === "aired.to") {
            $orderByField = "end_date";
        }

        if ($orderByField === "published.from") {
            $orderByField = "start_date";
        }

        if ($orderByField === "published.to") {
            $orderByField = "end_date";
        }
        // fixme end

        // override ordering field
        if (!is_null($orderByField) && in_array($orderByField, $modelAttrNames)) {
            $options['sort_by'] = "$orderByField:" . ($sortDirectionDescending ? "desc" : "asc") . ",_text_match(buckets:".$this->maxItemsPerPage."):desc";
        }

        // override overall sorting direction
        if (is_null($orderByField) && $sortDirectionDescending && array_key_exists("sort_by", $options) && Str::contains($options["sort_by"], "asc")) {
            $options["sort_by"] = Str::replace("asc", "desc", $options["sort_by"]);
        }

        return $options;
    }

    private function recordSearchTelemetry(string $query, array $typesenseApiResponse): void
    {
        $hits = collect($typesenseApiResponse["hits"]);
        $this->searchAnalytics->logSearch(
            $query,
            $typesenseApiResponse["found"],
            $hits->pluck('document')->values(),
            $typesenseApiResponse["request_params"]["collection_name"]
        );
    }
}
