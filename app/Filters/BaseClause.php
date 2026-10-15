<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class BaseClause
{
    protected $query;

    protected $filter;

    protected $values;

    public function __construct(mixed $values, mixed $filter)
    {
        $this->values = $values;
        $this->filter = $filter;
    }

    /**
     * @param  \Closure(mixed): mixed  $nextFilter
     */
    public function handle(mixed $query, \Closure $nextFilter): mixed
    {
        $query = $nextFilter($query);

        if (static::validate($this->values) === false) {
            return $query;
        }

        return static::apply($query);
    }

    /**
     * @param  mixed  $query
     * @return Builder<Model>
     */
    abstract protected function apply($query): Builder;

    abstract protected function validate(mixed $value): bool;
}
