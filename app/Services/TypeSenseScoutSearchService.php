<?php

namespace App\Services;

use App\JikanApiSearchableModel;
use Typesense\Documents;

class TypeSenseScoutSearchService implements ScoutSearchService
{
    /**
     * Executes a search operation via Laravel Scout on the provided model class.
     * @param object|string $modelClass
     * @param string $q
     * @return \Laravel\Scout\Builder
     * @throws \Http\Client\Exception
     * @throws \Typesense\Exceptions\TypesenseClientError
     */
    public function search(object|string $modelClass, string $q): \Laravel\Scout\Builder
    {
        return $modelClass::search($q, function (Documents $documents, string $query, array $options) use ($modelClass) {
            // let's enable exhaustive search
            // which will make Typesense consider all variations of prefixes and typo corrections of the words
            // in the query exhaustively, without stopping early when enough results are found.
            $options['exhaustive_search'] = true;
            $modelInstance = new $modelClass;
            // get the weights of the query_by fields, if they are provided by the model.
            if ($modelInstance instanceof JikanApiSearchableModel) {
                $queryByWeights = $modelInstance->getTypeSenseQueryByWeights();
                if (!is_null($queryByWeights)) {
                    $options['query_by_weights'] = $queryByWeights;
                }

                // if the model specifies search index sort order, use it
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
            }

            return $documents->search($options);
        });
    }
}
