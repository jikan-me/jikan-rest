<?php

namespace App\Services;

use Jenssegers\Mongodb\Query\Builder as MongoBuilder;

final class MongoSearchService extends SearchServiceBase
{
    public function search(string $searchTerms, ?string $orderByFields = null, bool $sortDirectionDescending = false): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        /**
         * @var MongoBuilder $query
         */
        $query = $this->query();

        /** @noinspection PhpParamsInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $query->whereRaw([
            '$text' => [
                '$search' => $searchTerms
            ],
        ], [
            'textMatchScore' => [
                '$meta' => 'textScore'
            ]
        ])->orderBy('textMatchScore', 'desc');
    }
}
