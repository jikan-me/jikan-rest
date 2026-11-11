<?php

namespace App\Services;

use Laravel\Scout\Builder;
use MongoDB\Laravel\Query\Builder as MongoBuilder;

final class MongoSearchService extends SearchServiceBase
{
    public function search(string $searchTerms, ?string $orderByFields = null, bool $sortDirectionDescending = false): Builder|\Illuminate\Database\Eloquent\Builder
    {
        /**
         * @var MongoBuilder $query
         */
        $query = $this->query();

        /** @noinspection PhpParamsInspection */
        $builder = $query->whereRaw([
            '$text' => [
                '$search' => $searchTerms,
            ],
        ], [
            'textMatchScore' => [
                '$meta' => 'textScore',
            ],
        ])->orderBy('textMatchScore', 'desc');

        if ($orderByFields !== null) {
            $order = explode(',', $orderByFields);
            foreach ($order as $o) {
                $builder = $builder->orderBy($o, $sortDirectionDescending ? 'desc' : 'asc');
            }
        }

        return $builder;
    }
}
