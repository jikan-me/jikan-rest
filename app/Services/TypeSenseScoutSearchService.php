<?php

namespace App\Services;

use App\Contracts\Repository;
use App\JikanApiSearchableModel;
use Illuminate\Support\Str;
use Typesense\Documents;

class TypeSenseScoutSearchService implements ScoutSearchService
{
    private int $maxItemsPerPage;

    public function __construct(private readonly Repository $repository)
    {
        $this->maxItemsPerPage = (int) env('MAX_RESULTS_PER_PAGE', 25);
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
        return $this->repository->search($q, function (Documents $documents, string $query, array $options) use ($orderByField, $sortDirectionDescending) {
            // let's enable exhaustive search
            // which will make Typesense consider all variations of prefixes and typo corrections of the words
            // in the query exhaustively, without stopping early when enough results are found.
            $options['exhaustive_search'] = env('TYPESENSE_SEARCH_EXHAUSTIVE', "true");
            $options['search_cutoff_ms'] = (int) env('TYPESENSE_SEARCH_CUTOFF_MS', 450);
            // this will be ignored together with exhaustive_search set to "true"
            $options['drop_tokens_threshold'] = (int) env('TYPESENSE_DROP_TOKENS_THRESHOLD', 1);
            $options['typo_tokens_threshold'] = (int) env('TYPESENSE_TYPO_TOKENS_THRESHOLD', 1);
            // prevent `Could not parse the filter query: unbalanced `&&` operands.` error
            // this adds support for typesense v0.24.1
            if (array_key_exists('filter_by', $options) && ($options['filter_by'] === ' && ' || $options['filter_by'] === '&&')) {
                unset($options['filter_by']);
            }

            if (array_key_exists('per_page', $options) && $options['per_page'] > 250) {
                $options['per_page'] = min($this->maxItemsPerPage, 250);
            }

            $modelInstance = $this->repository->createEntity();
            // get the weights of the query_by fields, if they are provided by the model.
            if ($modelInstance instanceof JikanApiSearchableModel) {
                $queryByWeights = $modelInstance->getTypeSenseQueryByWeights();
                if (!is_null($queryByWeights)) {
                    $options['query_by_weights'] = $queryByWeights;
                }

                // if the model specifies search index sort order, use it
                // this is the default sort order for the model
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

                // override ordering field
                if (!is_null($orderByField)) {
                    $options['sort_by'] = "$orderByField:" . ($sortDirectionDescending ? "desc" : "asc") . ",_text_match:desc";
                }

                // override overall sorting direction
                if (is_null($orderByField) && $sortDirectionDescending && array_key_exists("sort_by", $options) && Str::contains($options["sort_by"], "asc")) {
                    $options["sort_by"] = Str::replace("asc", "desc", $options["sort_by"]);
                }
            }

            return $documents->search($options);
        });
    }
}
