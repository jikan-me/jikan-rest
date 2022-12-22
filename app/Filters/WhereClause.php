<?php
namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class WhereClause extends BaseClause
{
    protected function apply($query): Builder
    {
        $method = is_array($this->values) ? 'orWhere' : 'andWhere';

        return $this->{$method}($query, $this->filter, $this->values);
    }

    protected function validate($value): bool
    {
        return !in_array(null, (array)$value);
    }

    private function orWhere($query, $filter, $values): Builder
    {
        $query->where(function($query) use($values, $filter) {
            foreach((array)$values as $value) {
                $query->orWhere($filter, $value);
            }
        });

        return $query;
    }

    private function andWhere($query, $filter, $values): Builder
    {
        foreach((array)$values as $value) {
            $query->where($filter, $value);
        }

        return $query;
    }
}
