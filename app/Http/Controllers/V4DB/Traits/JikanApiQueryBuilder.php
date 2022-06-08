<?php

namespace App\Http\Controllers\V4DB\Traits;

use App\Providers\SearchQueryBuilderProvider;
use Illuminate\Http\Request;

trait JikanApiQueryBuilder
{
    private SearchQueryBuilderProvider $searchQueryBuilderProvider;

    private function getQueryBuilder(string $name, Request $request): \Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder
    {
        $queryBuilder = $this->searchQueryBuilderProvider->getQueryBuilder($name);
        return $queryBuilder->query($request);
    }

    private function getPaginator(string $name, Request $request, \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $results): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $queryBuilder = $this->searchQueryBuilderProvider->getQueryBuilder($name);
        return $queryBuilder->paginateBuilder($request, $results);
    }

    /**
     * @template T
     * @param T $resourceCollectionClass
     * @param \Illuminate\Http\Request $request
     * @return T
     */
    private function preparePaginatedResponse(string|object $resourceCollectionClass, string $resourceTypeName, Request $request)
    {
        $results = $this->getQueryBuilder($resourceTypeName, $request);
        $paginator = $this->getPaginator($resourceTypeName, $request, $results);
        return new $resourceCollectionClass($paginator);
    }
}
