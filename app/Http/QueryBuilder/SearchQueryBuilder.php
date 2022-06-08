<?php

namespace App\Http\QueryBuilder;

use Illuminate\Http\Request;
use JetBrains\PhpStorm\ArrayShape;
use Laravel\Scout\Searchable;
use Jenssegers\Mongodb\Eloquent\Model;
use Typesense\Documents;

abstract class SearchQueryBuilder implements SearchQueryBuilderService
{
    protected array $commonParameterNames = ["q", "order_by", "sort", "letter"];
    protected array $parameterNames = [];
    protected string $displayNameFieldName = "name";
    protected bool $searchIndexesEnabled;

    private $modelClassTraitsCache = null;

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
     * @throws \Http\Client\Exception
     */
    private function getQueryBuilder($requestParameters): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        $modelClass = $this->getModelClass();

        if (!in_array(Model::class, class_parents($modelClass))) {
            throw new \Exception("Programming error: The getModelClass method should return a class which 
            inherits from \Jenssegers\Mongodb\Eloquent\Model.");
        }

        if ($this->isSearchIndexUsed() && !empty($requestParameters['q'])) {
            if (env('SCOUT_DRIVER') == 'typesense')
            {
                // if search index is typesense, let's enable exhaustive search
                // which will make Typesense consider all variations of prefixes and typo corrections of the words
                // in the query exhaustively, without stopping early when enough results are found.
                return $modelClass::search($requestParameters["q"], function(Documents $documents, string $query, array $options) {
                    $options['exhaustive_search'] = true;

                    return $documents->search($options);
                });
            }
            return $modelClass::search($requestParameters["q"]);
        }

        return $modelClass::query();
    }

    public function isSearchIndexUsed(): bool
    {
        $modelClass = $this->getModelClass();
        if (is_null($this->modelClassTraitsCache)) {
            $this->modelClassTraitsCache = class_uses_recursive($modelClass);
        }
        $traits = $this->modelClassTraitsCache;
        return in_array(Searchable::class, $traits) && $this->searchIndexesEnabled;
    }

    protected function sanitizeParameters($parameters): array
    {
        $parameters["sort"] = $this->mapSort($parameters["sort"]);
        $parameters["order_by"] = $this->mapOrderBy($parameters["order_by"]);

        return $parameters;
    }

    protected abstract function getModelClass(): object|string;

    protected abstract function buildQuery(array $requestParameters, \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $results): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder;

    protected abstract function getOrderByFieldMap(): array;

    /**
     * @throws \Exception
     * @throws \Http\Client\Exception
     */
    public function query(Request $request): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        $requestParameters = $this->getSanitizedParametersFromRequest($request);

        $results = $this->getQueryBuilder($requestParameters);

        // if search index is enabled, this way we only do the full-text search on the index, and filter further in mongodb.
        // the $results variable can be a Builder from the Mongodb Eloquent or from Scout. Both of them have a query
        // method which will result in a Mongodb Eloquent Builder.
        return $results->query(function(\Illuminate\Database\Eloquent\Builder $query) use($requestParameters) {
            $letter = $requestParameters['letter'];
            $q = $requestParameters['q'];

            if (!is_null($letter)) {
                $query = $query
                    ->where($this->displayNameFieldName, 'like', "{$letter}%");
            }

            if (empty($q)) {
                $query = $query
                    ->orderBy('mal_id');
            }

            // if search index is disabled, use mongo's full text-search
            if (!empty($q) && is_null($letter) && !$this->isSearchIndexUsed()) {
                /** @noinspection PhpParamsInspection */
                $query = $query
                    ->whereRaw([
                        '$text' => [
                            '$search' => $q
                        ],
                    ], [
                        'score' => [
                            '$meta' => 'textScore'
                        ]
                    ])
                    ->orderBy('score', ['$meta' => 'textScore']);
            }

            // The ->filter() call is a local model scope function, which applies filters based on the query string
            // parameters. This way we can simplify the code base and avoid a bunch of
            // "if ($this->request->get("asd")) { }" lines in controllers.
            $queryFilteredByQueryStringParams = $query->filter($requestParameters);
            return $this->buildQuery($requestParameters, $queryFilteredByQueryStringParams);
        });
    }

    #[ArrayShape(['per_page' => "int", 'total' => "int", 'current_page' => "int", 'last_page' => "int", 'data' => "array"])]
    public function paginate(Request $request, \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $results): array
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

    public function paginateBuilder(Request $request, \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $results): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        ['limit' => $limit, 'page' => $page] = $this->getPaginateParameters($request);

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
