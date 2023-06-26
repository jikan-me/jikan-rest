<?php

namespace App\Services;

use App\Contracts\Repository;
use App\JikanApiSearchableModel;
use App\Support\JikanConfig;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Typesense\Documents;

class TypeSenseScoutSearchService implements ScoutSearchService
{
    private int $maxItemsPerPage;

    public function __construct(private readonly Repository $repository, JikanConfig $config)
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
        return $this->repository->search($q, function (Documents $documents, string $query, array $options) use ($orderByField, $sortDirectionDescending) {
            // let's enable exhaustive search
            // which will make Typesense consider all variations of prefixes and typo corrections of the words
            // in the query exhaustively, without stopping early when enough results are found.
            $options['exhaustive_search'] = env('TYPESENSE_SEARCH_EXHAUSTIVE', "false");
            $options['search_cutoff_ms'] = (int) env('TYPESENSE_SEARCH_CUTOFF_MS', 450);
            // this will be ignored together with exhaustive_search set to "true"
            $options['drop_tokens_threshold'] = (int) env('TYPESENSE_DROP_TOKENS_THRESHOLD', 1);
            $options['typo_tokens_threshold'] = (int) env('TYPESENSE_TYPO_TOKENS_THRESHOLD', 1);
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

            // skip typo checking for short queries
            if (strlen($query) <= 3) {
                $options['num_typos'] = 0;
                $options['typo_tokens_threshold'] = 0;
                $options['drop_tokens_threshold'] = 0;
                $options['exhaustive_search'] = 'false';
                $options['infix'] = 'off';
                $options['prefix'] = 'false';
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

                // todo: try to avoid service lookup, resolve things via constructor instead.
                // this is currently a workaround as the search service resolution in the service provider is complex,
                // and it gives errors when you try to resolve the Typesense class from the LaraveTypesense driver package.
                // here we'd like to get all the searchable attributes of the model, so we can override the sort order.
                // we use these attribute names to validate the incoming field name against them, otherwise ignoring them.  
                $collectionDescriptor = App::make(TypesenseCollectionDescriptor::class);
                $modelAttrNames = $collectionDescriptor->getSearchableAttributes($modelInstance);

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
            }

            return $documents->search($options);
        });
    }
}
