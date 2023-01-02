<?php

namespace App\Support;

use App\Contracts\RepositoryQuery as RepositoryQueryContract;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder as ScoutBuilder;

class RepositoryQuery extends RepositoryQueryBase implements RepositoryQueryContract
{
    public function filter(Collection $params): EloquentBuilder|ScoutBuilder
    {
        return $this->queryable()->filter($params);
    }

    public function search(string $keywords, ?\Closure $callback = null): ScoutBuilder
    {
        return $this->searchable($keywords, $callback);
    }
}
