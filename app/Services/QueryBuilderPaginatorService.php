<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Represents a service which knows how to paginate the Scout/Eloquent database query builders.
 */
interface QueryBuilderPaginatorService
{
    function paginate(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $builder, ?int $limit = null, ?int $page = null): LengthAwarePaginator;
}
