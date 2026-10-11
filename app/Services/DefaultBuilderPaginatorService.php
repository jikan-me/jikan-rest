<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;

final class DefaultBuilderPaginatorService implements QueryBuilderPaginatorService
{
    public function __construct(
        private readonly EloquentBuilderPaginatorService $eloquentBuilderPaginatorService,
        private readonly ScoutBuilderPaginatorService $scoutBuilderPaginatorService) {}

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Model>|Builder<Model>  $builder
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, mixed>
     */
    public function paginate(\Illuminate\Database\Eloquent\Builder|Builder $builder, ?int $limit = null, ?int $page = null): LengthAwarePaginator
    {
        if ($builder instanceof Builder) {
            return $this->scoutBuilderPaginatorService->paginate($builder, $limit, $page);
        }

        return $this->eloquentBuilderPaginatorService->paginate($builder, $limit, $page);
    }
}
