<?php

namespace App\Http\QueryBuilder;

use App\Http\QueryBuilder\Traits\PaginationParameterResolver;
use App\Services\ScoutSearchService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\ArrayShape;
use Laravel\Scout\Searchable;
use Jenssegers\Mongodb\Eloquent\Model;

abstract class SearchQueryBuilder implements SearchQueryBuilderService
{
    use PaginationParameterResolver;

    protected array $commonParameterNames = ["q", "order_by", "sort", "letter"];
    protected array $parameterNames = [];
    protected string $displayNameFieldName = "name";
    protected bool $searchIndexesEnabled;
    private ScoutSearchService $scoutSearchService;

    private ?array $modelClassTraitsCache = null;

    public function __construct(bool $searchIndexesEnabled, ScoutSearchService $scoutSearchService)
    {
        $this->searchIndexesEnabled = $searchIndexesEnabled;
        $this->scoutSearchService = $scoutSearchService;
    }

    protected function getParametersFromRequest(Request $request): Collection
    {
        $paramNames = $this->getParameterNames();
        $parameters = [];

        foreach ($paramNames as $paramName) {
            $parameters[$paramName] = $request->get($paramName);
        }

        if (!array_key_exists("q", $parameters)) {
            $parameters["q"] = "";
        }

        return collect($parameters);
    }

    protected function getParameterNames(): array
    {
        return array_merge($this->commonParameterNames, $this->parameterNames);
    }

    protected function getSanitizedParametersFromRequest(Request $request): Collection
    {
        return $this->sanitizeParameters($this->getParametersFromRequest($request));
    }

    /**
     * @throws \Exception
     * @throws \Http\Client\Exception
     */
    private function getQueryBuilder(Collection $requestParameters): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        $modelClass = $this->getModelClass();

        if (!in_array(Model::class, class_parents($modelClass))) {
            throw new \Exception("Programming error: The getModelClass method should return a class which
            inherits from \Jenssegers\Mongodb\Eloquent\Model.");
        }

        $q = $requestParameters->get("q");

        if ($this->isSearchIndexUsed() && !empty($q)) {
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
            $builder = $this->scoutSearchService->search($modelClass, $q, $searchOptions["order_by"],
                $searchOptions["sort_direction_descending"]);
        } else {
            // If "q" is not set, OR search indexes are disabled, we just get a query builder for the model.
            // This way we can have a single place where we get the query builder from.
            $builder = $modelClass::query();
        }

        return $builder;
    }

    private function buildQueryInternal(Collection $requestParameters, mixed $query): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
    {
        $letter = $requestParameters->get('letter');
        $q = $requestParameters->get('q');
        $order_by = $requestParameters->get('order_by');

        if (!is_null($letter)) {
            $query = $query
                ->where($this->displayNameFieldName, 'like', "{$letter}%");
        }

        if (empty($q) && empty($order_by)) {
            $query = $query
                ->orderBy('mal_id');
        }

        // if search index is disabled, use mongo's full text-search
        if (!empty($q) && is_null($letter) && !$this->isSearchIndexUsed()) {
            $query = $query
                ->whereRaw([
                    '$text' => [
                        '$search' => $q
                    ],
                ], [
                    'textMatchScore' => [
                        '$meta' => 'textScore'
                    ]
                ])
                ->orderBy('textMatchScore', 'desc');
        }

        // The ->filter() call is a local model scope function, which applies filters based on the query string
        // parameters. This way we can simplify the code base and avoid a bunch of
        // "if ($this->request->get("asd")) { }" lines in controllers.
        $queryFilteredByQueryStringParams = $query->filter($requestParameters);
        return $this->buildQuery($requestParameters, $queryFilteredByQueryStringParams);
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

    public function isScoutBuilder(mixed $results): bool
    {
        return $results instanceof \Laravel\Scout\Builder;
    }

    protected function sanitizeParameters(Collection $parameters): Collection
    {
        $parameters["sort"] = $this->mapSort($parameters->get("sort"));
        $parameters["order_by"] = $this->mapOrderBy($parameters->get("order_by"));

        return $parameters;
    }

    protected abstract function getModelClass(): object|string;

    protected abstract function buildQuery(Collection $requestParameters, \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $results): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder;

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
        // the $results variable can be a Builder from the Mongodb Eloquent or from Scout. Only Laravel\Scout\Builder
        // has a query method which will result in a Mongodb Eloquent Builder.
        return $this->isScoutBuilder($results) ? $results->query(function (\Illuminate\Database\Eloquent\Builder $query) use ($requestParameters) {
            return $this->buildQueryInternal($requestParameters, $query);
        }) : $this->buildQueryInternal($requestParameters, $results);
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

    public function paginateBuilder(Request $request, \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder $results): LengthAwarePaginator
    {
        ['limit' => $limit, 'page' => $page] = $this->getPaginateParameters($request);

        if ($this->isSearchIndexUsed() && $this->isScoutBuilder($results)) {
            /**
             * @var \Laravel\Scout\Builder
             */
            $scoutBuilder = $results;
            // We want to influence the "getTotalCount" method of Scout's builder, so the pagination won't fail.
            // In that method the "$limit" member variable is being check whether it's null or it has value.
            // If it's set to a number then the result set will be limited which we do the pagination on.
            // If it's set to null, then the pagination will be done on the whole result set.
            /**
             * @var LengthAwarePaginator $paginated
             */
            $paginated = $scoutBuilder
                ->jikanPaginate(
                    $limit,
                    "page",
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
        $orderByFieldMap = collect($this->getOrderByFieldMap());

        return $orderByFieldMap->get($orderBy, null);
    }
}
