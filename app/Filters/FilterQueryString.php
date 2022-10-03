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
        'order_by' => OrderbyClause::class,
        'sort' => OrderbyClause::class
    ];

    /** @noinspection PhpUnused */
    public function scopeFilter(Builder $query, Collection $queryParameters)
    {
        $filters = $this->getFilters($queryParameters)->map(function ($values, $filter) {
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

    private function getFilters(Collection $queryParameters): Collection
    {
        $filter = function ($key) {

            $filters = $this->filters ?: [];

            // if model class sets the "unguardFilters" variable to true, then we skip the filter validation
            return !($this->unguardFilters != true) || in_array($key, $filters);
        };

        $result = collect(array_filter($queryParameters->all(), $filter, ARRAY_FILTER_USE_KEY))
                    ->filter(fn ($v, $k) => !empty($v)) ?? Collection::empty();

        return $this->_normalizeOrderBy($result);
    }
}
