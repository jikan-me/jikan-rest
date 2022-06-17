<?php
namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;

trait FilterQueryString
{
    use FilterResolver;

    private array $availableFilters = [
        'default' => WhereClause::class,
        'order_by' => OrderbyClause::class
    ];

    /** @noinspection PhpUnused */
    public function scopeFilter(Builder $query, Collection $queryParameters, ...$filters)
    {
        $filters = collect($this->getFilters($queryParameters, $filters))->map(function ($values, $filter) {
            return $this->resolve($filter, $values);
        })->toArray();

        return app(Pipeline::class)
            ->send($query)
            ->through($filters)
            ->thenReturn();
    }

    private function _normalizeOrderBy(Collection $filters): Collection
    {
        // fixme: this can be done more elegantly, for now this is here as a quick hack.
        if ($filters->offsetExists("sort")  && $filters->offsetExists("order_by")) {
            // we put the order by field and the sort direction in one array element.
            // the OrderByClause class will explode the string by the comma and set the correct field.
            $filters["order_by"] = $filters["order_by"] . "," . $filters["sort"];
            unset($filters["sort"]);
        }

        return $filters;
    }

    private function getFilters(Collection $queryParameters, array $filters): Collection
    {
        $filter = function ($key) use($filters) {

            $filters = $filters ?: $this->filters ?: [];

            // if model class sets the "unguardFilters" variable to true, then we skip the filter validation
            return !($this->unguardFilters != true) || in_array($key, $filters);
        };

        $result = $queryParameters->filter($filter) ?? Collection::empty();

        return $this->_normalizeOrderBy($result);
    }
}
