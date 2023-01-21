<?php

namespace App;

use App\Filters\FilterQueryString;
use Illuminate\Support\Collection;
use Jenssegers\Mongodb\Eloquent\Builder;

class JikanApiModel extends \Jenssegers\Mongodb\Eloquent\Model
{
    use FilterQueryString;

    /**
     * The list of parameters which can be used to filter the result-set from the database.
     * The available field names and "order_by" is allowed as values. If "order_by" is specified then the field name
     * from the  "order_by" query string parameter will be used to sort the results.
     * @var string[]
     */
    protected array $filters = [];

    /** @noinspection PhpUnused */
    public function scopeRandom(Builder $query, int $numberOfRandomItems = 1): Collection
    {
        return $query->raw(fn(\MongoDB\Collection $collection) => $collection->aggregate([
            ['$sample' => ['size' => $numberOfRandomItems]]
        ]));
    }
}
