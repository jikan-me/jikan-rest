<?php
namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

final class DefaultQueryBuilderService implements QueryBuilderService
{
    /**
     * @throws \Exception
     */
    public function __construct(
        private readonly SearchService $searchService,
        private readonly QueryBuilderPaginatorService $paginatorService
    )
    {
    }

    public function query(Collection $requestParameters): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        if ($requestParameters->get("q", "") !== "" && !$requestParameters->has("letter")) {
            $searchEngineOptions = $this->getSearchEngineOptions($requestParameters);
            $builder = $this->searchService->setFilterParameters($requestParameters)
                ->search($requestParameters->get("q"), $searchEngineOptions["order_by"], $searchEngineOptions["sort_direction_descending"]);
        } else {
            $builder = $this->searchService->setFilterParameters($requestParameters)->query();
        }

        return $builder;
    }

    public function paginate(\Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $builder, ?int $page = null, ?int $limit = null): array
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
            'data' => $items
        ];
    }

    public function paginateBuilder(\Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $builder, ?int $page = null, ?int $limit = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->paginatorService->paginate($builder, $limit, $page);
    }

    private function getSearchEngineOptions(Collection $requestParameters): array
    {
        $orderBy = $requestParameters->get("order_by");
        $sort = $requestParameters->get("sort");
        $searchOptions = [];

        // todo: validate whether the specified field exists on the model
        if (!empty($orderBy)) {
            $searchOptions["order_by"] = $orderBy;
        } else {
            $searchOptions["order_by"] = null;
        }

        if (!empty($sort) && in_array($sort, ["asc", "desc"])) {
            $searchOptions["sort_direction_descending"] = $sort == "desc";
        } else {
            $searchOptions["sort_direction_descending"] = false;
        }

        return $searchOptions;
    }
}
