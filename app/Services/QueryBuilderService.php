<?php

namespace App\Services;

use Illuminate\Support\Collection;

interface QueryBuilderService
{
    /**
     * @param Collection<array-key, mixed> $requestParameters
     * @return \Laravel\Scout\Builder<\Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Eloquent\Builder
     */
    function query(Collection $requestParameters):  \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder;

    /**
     * @param \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>|\Laravel\Scout\Builder<\Illuminate\Database\Eloquent\Model> $builder
     * @return array<string, mixed>
     */
    function paginate(\Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $builder, ?int $page = null, ?int $limit = null): array;

    /**
     * @param \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>|\Laravel\Scout\Builder<\Illuminate\Database\Eloquent\Model> $builder
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, mixed>
     */
    function paginateBuilder(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $builder, ?int $page = null, ?int $limit = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator;
}