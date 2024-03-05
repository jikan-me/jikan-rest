<?php

namespace App\Support;
use Laravel\Scout\Builder as ScoutBuilder;
use Illuminate\Contracts\Database\Query\Builder;
use Jenssegers\Mongodb\Eloquent\Builder as MongoDbBuilder;

class RepositoryQueryBase
{
    private ?Builder $queryableBuilder = null;
    private ?ScoutBuilder $searchableBuilder = null;

    public function __construct(
        private readonly \Closure $getQueryable,
        private readonly \Closure $getSearchable)
    {
    }

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

        return $this->queryableBuilder;
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

        return $this->searchableBuilder;
    }
}
