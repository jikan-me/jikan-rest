<?php

namespace App\Services;

use Illuminate\Support\Collection;

interface QueryBuilderService
{
    function query(Collection $requestParameters):  \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder;

    function paginate(\Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $builder, ?int $page = null, ?int $limit = null): array;

    function paginateBuilder(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $builder, ?int $page = null, ?int $limit = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator;
}
