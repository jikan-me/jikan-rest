<?php
namespace App\Mixins;

use Illuminate\Container\Container;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Laravel\Scout\Contracts\PaginatesEloquentModels;
use Typesense\LaravelTypesense\Engines\TypesenseEngine;

/** @mixin \Laravel\Scout\Builder */
class ScoutBuilderMixin
{
    /**
     * Specialised pagination. It retrieves all results from the search engine in chunks, loads them in memory
     * then paginates on the database results.
     * @noinspection PhpUnused
     */
    public function jikanPaginate(): \Closure
    {
        return function (int|null $perPage = null, string $pageName = 'page', int|null $page = null) {
            /** @var \Laravel\Scout\Builder $this */
            /** @var TypesenseEngine $engine */
            $engine = $this->engine();
            if ($engine instanceof PaginatesEloquentModels) {
                return $engine->paginate($this, $perPage, $page)->appends('query', $this->query);
            }

            $page = $page ?: Paginator::resolveCurrentPage($pageName);

            $perPage = $perPage ?: $this->model->getPerPage();
            $rawResults = $engine->paginate($this, max_results_per_page(), $page);
            $found = $engine->getTotalCount($rawResults);
            $results = $this->model->newCollection($engine->map($this, $rawResults, $this->model)->values()->all());

            return Container::getInstance()->makeWith(LengthAwarePaginator::class, [
                'items' => $results,
                'total' => $found,
                'perPage' => $perPage,
                'currentPage' => $page,
                'options' => [
                    'path' => Paginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ],
            ])->appends('query', $this->query);
        };
    }
}
