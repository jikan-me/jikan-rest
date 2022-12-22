<?php

namespace App\Services;

use App\JikanApiSearchableModel;
use ONGR\ElasticsearchDSL\Sort\FieldSort;

class ElasticScoutSearchService implements ScoutSearchService
{
    /**
     * Executes a search operation via Laravel Scout on the provided model class.
     * @param object|string $modelClass
     * @param string $q
     * @return \Laravel\Scout\Builder
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\MissingParameterException
     */
    public function search(object|string $modelClass, string $q, ?string $orderByField = null,
                           bool $sortDirectionDescending = false): \Laravel\Scout\Builder
    {
        return $modelClass::search($q, function(\Elastic\ElasticSearch\Client $client, \ONGR\ElasticsearchDSL\Search $body) use ($modelClass, $orderByField, $sortDirectionDescending) {
            $modelInstance = new $modelClass;

            if ($modelInstance instanceof JikanApiSearchableModel) {
                if (!is_null($orderByField)) {
                    $body->addSort(new FieldSort($orderByField, ['order' => $sortDirectionDescending ? FieldSort::DESC : FieldSort::ASC]));
                } else {
                    // if the model specifies search index sort order, use it
                    $sortByFields = $modelInstance->getSearchIndexSortBy();
                    if (!is_null($sortByFields)) {
                        foreach ($sortByFields as $f) {
                            $direction = match ($f['direction']) {
                                'asc' => FieldSort::ASC,
                                'desc' => FieldSort::DESC,
                            };

                            $sort = new FieldSort($f['field'], ['order' => $direction]);
                            $body->addSort($sort);
                        }
                    }
                }
            }

            return $client->search(['index' => $modelInstance->searchableAs(), 'body' => $body->toArray()]);
        });
    }
}
