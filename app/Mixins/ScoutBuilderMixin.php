<?php

namespace App\Mixins;

use App\JikanApiModel;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder;
use Laravel\Scout\Contracts\PaginatesEloquentModels;
use Laravel\Scout\Engines\TypesenseEngine;

/** @mixin Builder<Model> */
class ScoutBuilderMixin
{
    /**
     * Specialised pagination. It retrieves all results from the search engine in chunks, loads them in memory
     * then paginates on the database results.
     *
     * @noinspection PhpUnused
     */
    public function jikanPaginate(): \Closure
    {
        return function (?int $perPage = null, string $pageName = 'page', ?int $page = null) {
            /** @var Builder $this */
            /** @var TypesenseEngine $engine */
            $engine = $this->engine();
            if ($engine instanceof PaginatesEloquentModels) {
                return $engine->paginate($this, $perPage, $page)->appends('query', $this->query);
            }

            $page = $page ?: Paginator::resolveCurrentPage($pageName);
            $rawResults = $engine->paginate($this, max_results_per_page(), $page);
            $found = (int) $engine->getTotalCount($rawResults);
            $perPage = $perPage ?: $this->model->getPerPage();

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

    public function filter(): \Closure
    {
        return function (Collection $filterParameters): Builder {
            // Laravel binds this macro closure to a Scout Builder at runtime.
            /**
             * @var Builder<Model> $this
             *
             * @phpstan-ignore varTag.nativeType
             */
            if ($this->model instanceof JikanApiModel) {
                $model = $this->model;

                $filters = $model->getFilters($filterParameters)->map(function ($values, $filter) use ($model) {
                    return $model->resolve($filter, $values);
                })->toArray();

                return app(Pipeline::class)
                    ->send($this)
                    ->through($filters)
                    ->thenReturn();
            }

            return $this;
        };
    }
}
