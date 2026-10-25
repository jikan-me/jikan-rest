<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder;

final class DefaultQueryBuilderService implements QueryBuilderService
{
    /**
     * @throws \Exception
     */
    public function __construct(
        private readonly SearchService $searchService,
        private readonly QueryBuilderPaginatorService $paginatorService
    ) {}

    /**
     * @param  Collection<array-key, mixed>  $requestParameters
     * @return Builder<Model>|\Illuminate\Database\Eloquent\Builder
     */
    public function query(Collection $requestParameters): Builder|\Illuminate\Database\Eloquent\Builder
    {
        if ($requestParameters->get('q', '') !== '' && !$requestParameters->has('letter')) {
            $searchEngineOptions = $this->getSearchEngineOptions($requestParameters);
            $builder = $this->searchService->setFilterParameters($requestParameters)
                ->search($requestParameters->get('q'), $searchEngineOptions['order_by'], $searchEngineOptions['sort_direction_descending']);
        } else {
            $builder = $this->searchService->setFilterParameters($requestParameters)->query();
            if (!$requestParameters->has('order_by')) {
                $builder = $builder->orderBy('mal_id');
            }
        }

        return $builder;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Model>|Builder<Model>  $builder
     * @return array<string, mixed>
     */
    public function paginate(\Illuminate\Database\Eloquent\Builder|Builder $builder, ?int $page = null, ?int $limit = null): array
    {
        $paginated = $this->paginateBuilder($builder, $page, $limit);

        $items = $paginated->items();
        foreach ($items as &$item) {
            unset($item['_id']);
        }

        return [
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'data' => $items,
        ];
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Model>|Builder<Model>  $builder
     * @return LengthAwarePaginator<int, mixed>
     */
    public function paginateBuilder(\Illuminate\Database\Eloquent\Builder|Builder $builder, ?int $page = null, ?int $limit = null): LengthAwarePaginator
    {
        return $this->paginatorService->paginate($builder, $limit, $page);
    }

    /**
     * @param  Collection<array-key, mixed>  $requestParameters
     * @return array<string, mixed>
     */
    private function getSearchEngineOptions(Collection $requestParameters): array
    {
        $orderBy = $requestParameters->get('order_by');
        $sort = $requestParameters->get('sort');
        $searchOptions = [];

        // todo: validate whether the specified field exists on the model
        if (!empty($orderBy)) {
            $searchOptions['order_by'] = $orderBy;
        } else {
            $searchOptions['order_by'] = null;
        }

        if (!empty($sort) && in_array($sort, ['asc', 'desc'])) {
            $searchOptions['sort_direction_descending'] = $sort == 'desc';
        } else {
            $searchOptions['sort_direction_descending'] = false;
        }

        return $searchOptions;
    }
}
