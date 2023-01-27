<?php
namespace App\Filters;

use Illuminate\Support\Str;

trait FilterResolver
{
    private function resolve($filterName, $values)
    {
        if($this->isCustomFilter($filterName)) {
            return $this->resolveCustomFilter($filterName, $values);
        }

        $availableFilter = $this->availableFilters[$filterName] ?? $this->availableFilters['default'];

        return app($availableFilter, ['filter' => $filterName, 'values' => $values]);
    }

    private function resolveCustomFilter($filterName, $values)
    {
        $filterMethodName = "filterBy" . ucfirst(Str::camel($filterName));
        if (method_exists($this, $filterMethodName)) {
            $filterName = $filterMethodName;
        }
        return $this->getClosure($this->makeCallable($filterName), $values);
    }

    private function makeCallable($filter)
    {
        return static::class.'@'.$filter;
    }

    private function isCustomFilter($filterName)
    {
        return method_exists($this, $filterName) || method_exists($this, "filterBy" . ucfirst(Str::camel($filterName)));
    }

    private function getClosure($callable, $values)
    {
        return function ($query, $nextFilter) use ($callable, $values) {
            return app()->call($callable, ['query' => $nextFilter($query), 'value' => $values]);
        };
    }
}
