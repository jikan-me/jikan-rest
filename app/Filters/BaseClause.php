<?php
namespace App\Filters;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseClause
{
    protected $query;
    protected $filter;
    protected $values;

    public function __construct($values, $filter)
    {
        $this->values = $values;
        $this->filter = $filter;
    }

    public function handle($query, $nextFilter)
    {
        $query = $nextFilter($query);

        if(static::validate($this->values) === false) {
            return $query;
        }

        return static::apply($query);
    }

    abstract protected function apply($query): Builder;

    abstract protected function validate($value): bool;
}
