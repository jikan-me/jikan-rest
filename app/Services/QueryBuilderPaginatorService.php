<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Represents a service which knows how to paginate the Scout/Eloquent database query builders.
 */
interface QueryBuilderPaginatorService
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>|\Laravel\Scout\Builder<\Illuminate\Database\Eloquent\Model> $builder
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, mixed>
     */
    function paginate(\Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $builder, ?int $limit = null, ?int $page = null): LengthAwarePaginator;
}