<?php
namespace App;

use Laravel\Scout\Builder;
use Laravel\Scout\Searchable;

trait JikanSearchable
{
    use Searchable;

    private function flattenArrayWithKeys($array): array {
        $result = array();
        foreach($array as $key=>$value) {
            if(is_array($value)) {
                $result = $result + $this->flattenArrayWithKeys($value, $key . '.');
            }
            else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    protected function toTypeSenseCompatibleNestedField(string $fieldName): array {
        $field = $this->{$fieldName};
        if (!is_array($field) && !is_object($field)) {
            return $field;
        }

        return $this->flattenArrayWithKeys($field);
    }

    protected function getMalIdsOfField(mixed $field): array {
        return array_map(function($elem) {
            return $elem->mal_id;
        }, $field);
    }

    public function queryScoutModelsByIds(Builder $builder, array $ids): Builder
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
