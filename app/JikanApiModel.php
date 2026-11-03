<?php

namespace App;

use App\Filters\FilterQueryString;
use Illuminate\Support\Collection;
use MongoDB\Laravel\Eloquent\Builder;
use MongoDB\Laravel\Eloquent\Model;

/**
 * Mongo-backed base model. Attributes are magic (from the collection);
 * documented below are the shared ones the app reads.
 *
 * @property bool|null $unguardFilters
 */
class JikanApiModel extends Model
{
    use FilterQueryString;

    /**
     * The list of parameters which can be used to filter the result-set from the database.
     * The available field names and "order_by" is allowed as values. If "order_by" is specified then the field name
     * from the  "order_by" query string parameter will be used to sort the results.
     *
     * @var string[]
     */
    protected array $filters = [];

    /**
     * Field => value pairs an item must not have to count as sfw; models with adult content override it.
     *
     * @return array<string, string|int>
     */
    public function sfwExclusions(): array
    {
        return [];
    }

    /** @noinspection PhpUnused */
    public function scopeRandom(Builder $query, int $numberOfRandomItems = 1, bool $sfw = false, bool $unapproved = false): Collection
    {
        return $query->raw(function (\MongoDB\Collection $collection) use ($numberOfRandomItems, $sfw, $unapproved) {
            $sfwFilter = array_map(fn ($excludedValue) => ['$ne' => $excludedValue], $this->sfwExclusions());

            $pipelineParams = [
                ['$sample' => ['size' => $numberOfRandomItems]],
            ];

            if ($sfw && $unapproved) {
                array_unshift($pipelineParams, [
                    '$match' => [
                        ...$sfwFilter,
                        'approved' => false,
                    ],
                ]);
            } elseif ($sfw) {
                array_unshift($pipelineParams, ['$match' => $sfwFilter]);
            } elseif ($unapproved) {
                array_unshift($pipelineParams, ['$match' => ['approved' => false]]);
            }

            return $collection->aggregate($pipelineParams);
        });
    }
}
