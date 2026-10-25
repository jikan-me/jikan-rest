<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class WhereClause extends BaseClause
{
    /**
     * @param  mixed  $query
     * @return Builder<Model>
     */
    protected function apply($query): Builder
    {
        $method = is_array($this->values) ? 'orWhere' : 'andWhere';

        return $this->{$method}($query, $this->filter, $this->values);
    }

    protected function validate(mixed $value): bool
    {
        return !in_array(null, (array) $value);
    }

    /**
     * @param  mixed  $query
     * @return Builder<Model>
     */
    private function orWhere($query, $filter, $values): Builder
    {
        $query->where(function ($query) use ($values, $filter) {
            foreach ((array) $values as $value) {
                $query->orWhere($filter, $value);
            }
        });

        return $query;
    }

    /**
     * @return Builder<Model>
     */
    private function andWhere($query, $filter, $values): Builder
    {
        foreach ((array) $values as $value) {
            $query->where($filter, $value);
        }

        return $query;
    }
}
