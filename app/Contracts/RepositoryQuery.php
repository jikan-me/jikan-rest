<?php

namespace App\Contracts;

use App\JikanApiModel;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder as ScoutBuilder;

/**
 * @template T of JikanApiModel
 */
interface RepositoryQuery
{
    /**
     * @param Collection $params
     * @return Builder<T>|ScoutBuilder<T>
     */
    public function filter(Collection $params): Builder|ScoutBuilder;

    /**
     * @param string $keywords
     * @param \Closure|null $callback
     * @return ScoutBuilder<T>
     */
    public function search(string $keywords, ?\Closure $callback = null): ScoutBuilder;

    /**
     * Get a where filter query
     * @param string $key
     * @param mixed $value
     * @return Builder
     */
    public function where(string $key, mixed $value): Builder;
}
