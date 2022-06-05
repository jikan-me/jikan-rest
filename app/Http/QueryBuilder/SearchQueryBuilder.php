<?php

namespace App\Http\QueryBuilder;

use Illuminate\Http\Request;
use JetBrains\PhpStorm\ArrayShape;
use Laravel\Scout\Searchable;
use Jenssegers\Mongodb\Eloquent\Model;

abstract class SearchQueryBuilder implements SearchQueryBuilderService
{
    protected array $commonParameterNames = ["q", "order_by", "sort", "letter"];
    protected array $parameterNames = [];
    protected string $displayNameFieldName = "name";
    protected bool $searchIndexesEnabled;

    public function __construct(bool $searchIndexesEnabled)
    {
        $this->searchIndexesEnabled = $searchIndexesEnabled;
    }

    protected function getParametersFromRequest(Request $request): array
    {
        $paramNames = $this->getParameterNames();
        $parameters = [];

        foreach ($paramNames as $paramName) {
            $parameters[$paramName] = $request->get($paramName);
        }

        if (!array_key_exists("q", $parameters)) {
            $parameters["q"] = "";
        }

        return $parameters;
    }

    protected function getParameterNames(): array
    {
        return array_merge($this->commonParameterNames, $this->parameterNames);
    }

    protected function getSanitizedParametersFromRequest(Request $request): array
    {
        return $this->sanitizeParameters($this->getParametersFromRequest($request));
    }

    /**
     * @throws \Exception
     */
    private function getQueryBuilder($requestParameters): \Laravel\Scout\Builder|\Jenssegers\Mongodb\Eloquent\Builder
    {
        $modelClass = $this->getModelClass();
        $traits = class_uses_recursive($modelClass);

        if (!in_array(Model::class, class_parents($modelClass))) {
            throw new \Exception("Programming error: The getModelClass method should return a class which 
            inherits from \Jenssegers\Mongodb\Eloquent\Model.");
        }

        if ($this->isSearchIndexUsed()) {
            return $modelClass::search($requestParameters["q"]);
        }

        return $modelClass::query();
    }

    public function isSearchIndexUsed(): bool
    {
        $modelClass = $this->getModelClass();
        $traits = class_uses_recursive($modelClass);
        return in_array(Searchable::class, $traits) && $this->searchIndexesEnabled;
    }

    protected function sanitizeParameters($parameters): array
    {
        $parameters["sort"] = $this->mapSort($parameters["sort"]);
        $parameters["order_by"] = $this->mapOrderBy($parameters["order_by"]);

        return $parameters;
    }

    protected abstract function getModelClass(): object|string;

    protected abstract function buildQuery(array $requestParameters, \Laravel\Scout\Builder|\Jenssegers\Mongodb\Eloquent\Builder $results): \Laravel\Scout\Builder|\Jenssegers\Mongodb\Eloquent\Builder;

    protected abstract function getOrderByFieldMap(): array;

    /**
     * @throws \Exception
     */
    public function query(Request $request): \Laravel\Scout\Builder|\Jenssegers\Mongodb\Eloquent\Builder
    {
        $requestParameters = $this->getSanitizedParametersFromRequest($request);
        extract($requestParameters);
        $results = $this->getQueryBuilder($requestParameters);

        if (!is_null($letter)) {
            $results = $results
                ->where($this->displayNameFieldName, 'like', "{$letter}%");
        }

        if (!is_null($order_by)) {
            $results = $results
                ->orderBy($order_by, $sort ?? 'asc');
        }

        if (empty($q)) {
            $results = $results
                ->orderBy('mal_id');
        }

        // if search index is disabled, use mongo's full text-search
        if (empty($q) && is_null($letter) && !$this->isSearchIndexUsed()) {
            $results = $results
                ->whereRaw([
                    '$text' => [
                        '$search' => $query
                    ],
                ], [
                    'score' => [
                        '$meta' => 'textScore'
                    ]
                ])
                ->orderBy('score', ['$meta' => 'textScore']);
        }

        return $this->buildQuery($requestParameters, $results);
    }

    #[ArrayShape(['per_page' => "int", 'total' => "int", 'current_page' => "int", 'last_page' => "int", 'data' => "array"])]
    public function paginate(Request $request, \Laravel\Scout\Builder|\Jenssegers\Mongodb\Eloquent\Builder $results): array
    {
        $paginated = $this->paginateBuilder($request, $results);

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

    private function getPaginateParameters(Request $request): array
    {
        $page = $request->get('page') ?? 1;
        $limit = $request->get('limit') ?? env('MAX_RESULTS_PER_PAGE', 25);

        $limit = (int)$limit;

        if ($limit <= 0) {
            $limit = 1;
        }

        if ($limit > env('MAX_RESULTS_PER_PAGE', 25)) {
            $limit = env('MAX_RESULTS_PER_PAGE', 25);
        }

        if ($page <= 0) {
            $page = 1;
        }

        return compact("page", "limit");
    }

    public function paginateBuilder(Request $request, \Laravel\Scout\Builder|\Jenssegers\Mongodb\Eloquent\Builder $results): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        extract($this->getPaginateParameters($request));

        if ($this->isSearchIndexUsed()) {
            $paginated = $results
                ->paginate(
                    $limit,
                    null,
                    null,
                    $page
                );
        } else {
            $paginated = $results
                ->paginate(
                    $limit,
                    ['*'],
                    null,
                    $page
                );
        }

        return $paginated;
    }

    /**
     * @param string|null $sort
     * @return string|null
     */
    public function mapSort(?string $sort = null): ?string
    {
        $sort = strtolower($sort);

        return $sort === 'desc' ? 'desc' : 'asc';
    }

    /**
     * @param string|null $orderBy
     * @return string|null
     */
    public function mapOrderBy(?string $orderBy): ?string
    {
        $orderBy = strtolower($orderBy);

        return $this->getOrderByFieldMap()[$orderBy] ?? null;
    }
}
