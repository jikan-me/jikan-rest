<?php
namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pipeline\Pipeline;

trait FilterQueryString
{
    use FilterResolver;

    private array $availableFilters = [
        'default' => WhereClause::class,
        'order_by' => OrderbyClause::class
    ];

    /** @noinspection PhpUnused */
    public function scopeFilter(Builder $query, array $queryParameters, ...$filters)
    {
        $filters = collect($this->getFilters($queryParameters, $filters))->map(function ($values, $filter) {
            return $this->resolve($filter, $values);
        })->toArray();

        return app(Pipeline::class)
            ->send($query)
            ->through($filters)
            ->thenReturn();
    }

    private function _normalizeOrderBy(array $filters): array
    {
        // fixme: this can be done more elegantly, for now this is here as a quick hack.
        if (array_key_exists("sort", $filters) && array_key_exists("order_by", $filters)) {
            // we put the order by field and the sort direction in one array element.
            // the OrderByClause class will explode the string by the comma and set the correct field.
            $filters["order_by"] = $filters["order_by"] . "," . $filters["sort"];
            unset($filters["sort"]);
        }

        return $filters;
    }

    private function getFilters(array $queryParameters, array $filters): array
    {
        $filter = function ($key) use($filters) {

            $filters = $filters ?: $this->filters ?: [];

            // if model class sets the "unguardFilters" variable to true, then we skip the filter validation
            return !($this->unguardFilters != true) || in_array($key, $filters);
        };

        $result = array_filter($queryParameters, $filter, ARRAY_FILTER_USE_KEY) ?? [];

        return $this->_normalizeOrderBy($result);
    }
}
