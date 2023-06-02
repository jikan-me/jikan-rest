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

            // custom pagination, where we only paginate on the db, not in the search engine
            // we do chunked processing of hits from the search engine if the number of hits exceed 250
            $searchEngineResultChunkSize = 250;
            $rawResults = $engine->paginate($this, $searchEngineResultChunkSize, 1);
            $found = $engine->getTotalCount($rawResults);
            if ($found > 250) {
                $additionalPages = $found / $searchEngineResultChunkSize;
                $currentAdditionalPage = 1.0;
                while ($currentAdditionalPage <= $additionalPages) {
                    $temp = $engine->paginate($this, $searchEngineResultChunkSize, 1 + round($currentAdditionalPage));
                    $rawResults = array_merge_recursive($rawResults, $temp);
                    $currentAdditionalPage += 1.0;
                }
            }

            // Notice forPage call here. We use that to only get the records for the current page from db.
            $results = $this->model->newCollection($engine->map(
                $this, $rawResults, $this->model
            )->forPage($page, $perPage)->values()->all());

            return Container::getInstance()->makeWith(LengthAwarePaginator::class, [
                'items' => $results,
                'total' => $this->getTotalCount($rawResults),
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
