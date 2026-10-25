<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder;

interface QueryBuilderService
{
    /**
     * @param  Collection<array-key, mixed>  $requestParameters
     * @return Builder<Model>|\Illuminate\Database\Eloquent\Builder
     */
    public function query(Collection $requestParameters): Builder|\Illuminate\Database\Eloquent\Builder;

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Model>|Builder<Model>  $builder
     * @return array<string, mixed>
     */
    public function paginate(\Illuminate\Database\Eloquent\Builder|Builder $builder, ?int $page = null, ?int $limit = null): array;

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Model>|Builder<Model>  $builder
     * @return LengthAwarePaginator<int, mixed>
     */
    public function paginateBuilder(Builder|\Illuminate\Database\Eloquent\Builder $builder, ?int $page = null, ?int $limit = null): LengthAwarePaginator;
}
