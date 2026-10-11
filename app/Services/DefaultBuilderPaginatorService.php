<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class DefaultBuilderPaginatorService implements QueryBuilderPaginatorService
{
    public function __construct(
        private readonly EloquentBuilderPaginatorService $eloquentBuilderPaginatorService,
        private readonly ScoutBuilderPaginatorService $scoutBuilderPaginatorService)
    {
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>|\Laravel\Scout\Builder<\Illuminate\Database\Eloquent\Model> $builder
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, mixed>
     */
    public function paginate(\Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $builder, ?int $limit = null, ?int $page = null): LengthAwarePaginator
    {
        if ($builder instanceof  \Laravel\Scout\Builder) {
            return $this->scoutBuilderPaginatorService->paginate($builder, $limit, $page);
        }

        return $this->eloquentBuilderPaginatorService->paginate($builder, $limit, $page);
    }
}