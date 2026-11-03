<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;

/**
 * Represents a service which knows how to paginate the Scout/Eloquent database query builders.
 */
interface QueryBuilderPaginatorService
{
    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Model>|Builder<Model>  $builder
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, mixed>
     */
    public function paginate(\Illuminate\Database\Eloquent\Builder|Builder $builder, ?int $limit = null, ?int $page = null): LengthAwarePaginator;
}
