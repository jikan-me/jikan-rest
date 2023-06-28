<?php

namespace App\Services;

use App\Contracts\Repository;
use App\JikanApiSearchableModel;
use App\Support\JikanConfig;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Typesense\Documents;
use App\Contracts\SearchAnalyticsService;
use App\Services\TypesenseCollectionDescriptor;

class TypeSenseScoutSearchService implements ScoutSearchService
{
    private int $maxItemsPerPage;

    public function __construct(private readonly Repository $repository,
                    JikanConfig $config,
                    private readonly TypesenseCollectionDescriptor $collectionDescriptor,
                    private readonly SearchAnalyticsService $searchAnalytics)
    {
        $this->maxItemsPerPage = (int) $config->maxResultsPerPage();
        if ($this->maxItemsPerPage > 250) {
            $this->maxItemsPerPage = 250;
        }
    }

    /**
     * Executes a search operation via Laravel Scout on the provided model class.
     * @param string $q
     * @return \Laravel\Scout\Builder
     * @throws \Http\Client\Exception
     * @throws \Typesense\Exceptions\TypesenseClientError
     */
    public function search(string $q, ?string $orderByField = null,
                           bool $sortDirectionDescending = false): \Laravel\Scout\Builder
    {
        return $this->repository->search($q, $this->middleware($orderByField, $sortDirectionDescending));
    }

    private function middleware(?string $orderByField = null, bool $sortDirectionDescending = false): \Closure
    {
        return function (Documents $documents, string $query, array $options) use ($orderByField, $sortDirectionDescending) {
            // let's enable exhaustive search
            // which will make Typesense consider all variations of prefixes and typo corrections of the words
            // in the query exhaustively, without stopping early when enough results are found.
            $options['exhaustive_search'] = env('TYPESENSE_SEARCH_EXHAUSTIVE', "false");
            $options['search_cutoff_ms'] = (int) env('TYPESENSE_SEARCH_CUTOFF_MS', 450);
            // this will be ignored together with exhaustive_search set to "true"
            $options['drop_tokens_threshold'] = (int) env('TYPESENSE_DROP_TOKENS_THRESHOLD', $this->maxItemsPerPage);
            $options['typo_tokens_threshold'] = (int) env('TYPESENSE_TYPO_TOKENS_THRESHOLD', $this->maxItemsPerPage);
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
        if (!is_null($orderByField) && Arr::has($modelAttrNames, $orderByField)) {
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
