<?php

namespace App\Contracts;

use App\JikanApiModel;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder as ScoutBuilder;

/**
 * @template T of JikanApiModel
 */
interface RepositoryQuery
{
    /**
     * @param Collection $params
     * @return EloquentBuilder<T>|ScoutBuilder<T>
     */
    public function filter(Collection $params): EloquentBuilder|ScoutBuilder;

    /**
     * @param string $keywords
     * @param \Closure|null $callback
     * @return ScoutBuilder<T>
     */
    public function search(string $keywords, ?\Closure $callback = null): ScoutBuilder;
}
