<?php

namespace App\Support;
use Laravel\Scout\Builder as ScoutBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class RepositoryQueryBase
{
    private ?EloquentBuilder $queryableBuilder;
    private ?ScoutBuilder $searchableBuilder;

    public function __construct(
        private readonly \Closure $getQueryable,
        private readonly \Closure $getSearchable)
    {
    }

    protected function queryable(bool $createNew = false): EloquentBuilder
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
