<?php

namespace App\Services;

use App\Concerns\ResolvesPaginatorParams;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;

final class EloquentBuilderPaginatorService implements QueryBuilderPaginatorService
{
    use ResolvesPaginatorParams;

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Model>|Builder<Model>  $builder
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, mixed>
     */
    public function paginate(\Illuminate\Database\Eloquent\Builder|Builder $builder, ?int $limit = null, ?int $page = null): LengthAwarePaginator
    {
        ['limit' => $limit, 'page' => $page] = $this->getPaginatorParams($limit, $page);

        return $builder->paginate($limit, ['*'], null, $page);
    }
}
