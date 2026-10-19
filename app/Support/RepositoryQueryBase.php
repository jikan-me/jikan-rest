<?php

namespace App\Support;

use Illuminate\Contracts\Database\Query\Builder;
use Laravel\Scout\Builder as ScoutBuilder;
use MongoDB\Laravel\Eloquent\Builder as MongoDbBuilder;

class RepositoryQueryBase
{
    private ?Builder $queryableBuilder = null;

    private ?ScoutBuilder $searchableBuilder = null;

    public function __construct(
        private readonly \Closure $getQueryable,
        private readonly \Closure $getSearchable) {}

    protected function queryable(bool $createNew = false): Builder|MongoDbBuilder
    {
        if ($createNew) {
            $callback = $this->getQueryable;

            return $callback();
        }

        if ($this->queryableBuilder === null) {
            $callback = $this->getQueryable;
            $this->queryableBuilder = $callback();
        }

        // Builders are mutable (`where()` mutates in place). Hand out a copy so
        // clauses added by one caller can never accumulate on the cached
        // instance and bleed into later requests on a persistent worker.
        return clone $this->queryableBuilder;
    }

    protected function searchable(string $keywords, ?\Closure $callback = null, bool $createNew = false): ScoutBuilder
    {
        if ($createNew) {
            $getSearchable = $this->getSearchable;

            return $getSearchable($keywords, $callback);
        }

        if ($this->searchableBuilder === null) {
            $getSearchable = $this->getSearchable;
            $this->searchableBuilder = $getSearchable($keywords, $callback);
        }

        return clone $this->searchableBuilder;
    }
}
