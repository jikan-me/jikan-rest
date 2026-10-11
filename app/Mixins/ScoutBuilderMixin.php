<?php

namespace App\Mixins;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
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

            $perPage = $perPage ?: $this->model->getPerPage();

            // custom pagination, where we only paginate on the db, not in the search engine
            // we do chunked processing of hits from the search engine if the number of hits exceed 250
            $searchEngineResultChunkSize = 250;
            $firstPage = $engine->paginate($this, $searchEngineResultChunkSize, 1);
            $found = (int) $engine->getTotalCount($firstPage);

            $hits = $firstPage['hits'] ?? [];
            while (count($hits) < $found) {
                $nextPage = $engine->paginate(
                    $this,
                    $searchEngineResultChunkSize,
                    (int) (count($hits) / $searchEngineResultChunkSize) + 1
                );
                $nextHits = $nextPage['hits'] ?? [];
                if ($nextHits === []) {
                    break; // engine returned fewer than 'found' (consistency/estimation)
                }
                $hits = array_merge($hits, $nextHits);
            }

            // Keep 'found' scalar: array_merge_recursive() previously merged the
            // page responses element-wise, turning 'found' (and the nested hits)
            // into nested arrays and producing a bogus total via getTotalCount().
            $rawResults = ['hits' => $hits, 'found' => $found];

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
