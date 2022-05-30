<?php
namespace App;

use Laravel\Scout\Builder;
use Laravel\Scout\Searchable;

trait JikanSearchable
{
    use Searchable;

    public function queryScoutModelsByIds(Builder $builder, array $ids)
    {
        $query = static::usesSoftDelete()
            ? $this->withTrashed() : $this->newQuery();

        if ($builder->queryCallback) {
            call_user_func($builder->queryCallback, $query);
        }

        $whereIn = in_array($this->getKeyType(), ['int', 'integer']) ?
            'whereIntegerInRaw' :
            'whereIn';

        return $query->{$whereIn}(
            $this->getScoutKeyName(), array_map(function($v) { return (int)$v; }, $ids)
        );
    }
}
