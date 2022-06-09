<?php

namespace App\Http\QueryBuilder\Traits;

trait TopQueryFilterResolver
{
    protected array $filterMap = [];

    private function mapFilter(?string $filter = null) : ?string
    {
        $filter = strtolower($filter);

        if (!\in_array($filter, $this->filterMap)) {
            return null;
        }

        return $filter;
    }
}
