<?php
namespace App;
use MongoDB\BSON\ObjectId;
use Laravel\Scout\Builder;

class SearchMetric extends JikanApiSearchableModel
{
    protected $table = 'search_metrics';

    protected $appends = ['hits'];

    protected $fillable = ["search_term", "request_count", "hits", "hits_count", "index_name"];

    protected $hidden = ["_id"];

    public function searchableAs(): string
    {
        return "jikan_search_metrics";
    }

    public function toSearchableArray()
    {
        return [
            "id" => $this->_id,
            "search_term" => $this->search_term,
            "request_count" => $this->request_count,
            "hits" => $this->hits,
            "hits_count" => $this->hits_count,
            "index_name" => $this->index_name
        ];
    }

    public function getCollectionSchema(): array
    {
        return [
            "name" => $this->searchableAs(),
            "fields" => [
                [
                    "name" => "id",
                    "type" => "string",
                    "optional" => false
                ],
                [
                    "name" => "search_term",
                    "type" => "string",
                    "optional" => false,
                    "sort" => false,
                    "infix" => true
                ],
                [
                    "name" => "request_count",
                    "type" => "int64",
                    "sort" => true,
                    "optional" => false,
                ],
                [
                    "name" => "hits",
                    "type" => "int64[]",
                    "sort" => false,
                    "optional" => false,
                ],
                [
                    "name" => "hits_count",
                    "type" => "int64",
                    "sort" => true,
                    "optional" => false,
                ],
                [
                    "name" => "index_name",
                    "type" => "string",
                    "sort" => false,
                    "optional" => false,
                    "facet" => true
                ]
            ]
        ];
    }

    public function getTypeSenseQueryByWeights(): string|null
    {
        return "1";
    }

    public function getScoutKey(): mixed
    {
        return $this->_id;
    }

    public function getScoutKeyName(): mixed
    {
        return '_id';
    }

    public function getKeyType(): string
    {
        return 'string';
    }

    public function typesenseQueryBy(): array
    {
        return ["search_term"];
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
            $this->getScoutKeyName(), array_map(fn ($x) => new ObjectId($x), $ids)
        );
    }
}
