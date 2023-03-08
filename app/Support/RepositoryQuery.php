<?php

namespace App\Support;

use App\Contracts\RepositoryQuery as RepositoryQueryContract;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder as ScoutBuilder;

class RepositoryQuery extends RepositoryQueryBase implements RepositoryQueryContract
{
    public function filter(Collection $params): Builder|ScoutBuilder
    {
        return $this->queryable()->filter($params);
    }

    public function search(string $keywords, ?\Closure $callback = null): ScoutBuilder
    {
        return $this->searchable($keywords, $callback, true);
    }

    public function where(string $key, mixed $value): Builder
    {
        return $this->queryable()->where($key, $value);
    }
}
