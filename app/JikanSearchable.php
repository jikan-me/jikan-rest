<?php
namespace App;

use Jikan\Helper\Parser;
use Laravel\Scout\Builder;
use Laravel\Scout\Searchable;

trait JikanSearchable
{
    use Searchable;

    protected function toTypeSenseCompatibleNestedField(string $fieldName): array
    {
        $field = $this->{$fieldName};

        if (!is_array($field) && !is_object($field)) {
            return $field;
        }

        return collect($field)->to2dArrayWithDottedKeys($field, $fieldName.'.');
    }

    protected function getMalIdsOfField(mixed $field): array
    {
        return array_map(function($elem) {
            return $elem["mal_id"];
        }, $field);
    }

    protected function convertToTimestamp(?string $datetime): int
    {
        return $datetime ? Parser::parseDate($datetime)->getTimestamp() : 0;
    }

    public function queryScoutModelsByIds(Builder $builder, array $ids): \Laravel\Scout\Builder|\Illuminate\Database\Eloquent\Builder
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
            $this->getScoutKeyName(), array_map(fn ($v) => (int)$v, $ids)
        );
    }
}
