<?php

namespace App\Repositories;

use App\Anime;
use App\Contracts\AnimeRepository;
use App\Contracts\Repository;
use App\Enums\AnimeScheduleFilterEnum;
use App\Enums\AnimeStatusEnum;
use App\Enums\AnimeTypeEnum;
use Illuminate\Contracts\Database\Query\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Laravel\Scout\Builder as ScoutBuilder;

/**
 * @implements Repository<Anime>
 */
final class DefaultAnimeRepository extends DatabaseRepository implements AnimeRepository
{
    public function __construct()
    {
        parent::__construct(fn () => Anime::query(), fn ($x, $y) => Anime::search($x, $y));
    }

    public function getTopAiringItems(): EloquentBuilder|ScoutBuilder
    {
        return $this
            ->orderByScore()
            ->where("airing", true);
    }

    public function getTopUpcomingItems(): EloquentBuilder|ScoutBuilder
    {
        return $this
            ->orderByPopularity()
            ->where("status", AnimeStatusEnum::upcoming()->label);
    }

    public function exceptItemsWithAdultRating(): EloquentBuilder|ScoutBuilder
    {
        $builder = $this->queryable();

        $this->excludeNsfwItems($builder);
        return $builder;
    }

    public function excludeNsfwItems($builder): EloquentBuilder|ScoutBuilder
    {
        return $builder->exceptItemsWithAdultRating();
    }

    public function excludeUnapprovedItems($builder): Collection|EloquentBuilder|ScoutBuilder
    {
        return $builder
            ->where("approved", true);
    }

    public function excludeKidsItems($builder): EloquentBuilder|ScoutBuilder
    {
        return $builder->exceptKidsItems();
    }

    public function orderByPopularity(): EloquentBuilder|ScoutBuilder
    {
        return $this
            ->queryable()
            ->orderBy("members", "desc");
    }

    public function orderByFavoriteCount(): EloquentBuilder|ScoutBuilder
    {
        return $this
            ->queryable()
            ->orderBy("favorites", "desc");
    }

    public function orderByRank(): EloquentBuilder|ScoutBuilder
    {
        return $this
            ->queryable()
            ->whereNotNull("rank")
            ->where("rank", ">", 0)
            ->orderBy("rank");
    }

    public function getCurrentlyAiring(
        ?AnimeScheduleFilterEnum $filter = null
    ): EloquentBuilder
    {
        /*
         * all have status as currently airing
         * all have premiered, but they're not necessarily the current season or year
         * all have aired date, but they're not necessarily the current date/season
         */
        $queryable = $this->queryable(true)
                          ->orderBy("members")
                          ->where("type", AnimeTypeEnum::tv()->label)
                          ->where("status", AnimeStatusEnum::airing()->label);

        if (!is_null($filter)) {
            if ($filter->isWeekDay()) {
                $queryable = $queryable->where("broadcast", "like", "{$filter->label}%");
            }
            else {
                $queryable = $queryable->where("broadcast", $filter->label);
            }
        }

        return $queryable;
    }

    public function getAiredBetween(
        Carbon $from,
        Carbon $to,
        ?AnimeTypeEnum $type = null,
        ?string $premiered = null
    ): EloquentBuilder
    {
        $queryable = $this->queryable(true);

        $airedFilter = ['aired.from' => [
            '$gte' => $from->toAtomString(),
            '$lte' => $to->modify('last day of this month')->toAtomString()
        ]];

        $finalFilter = [];

        // if the premiered parameter for the filter is not null, look for those items which have a premiered attribute set,
        // and equals to the parameter value, OR look for those items which doesn't have premired attribute set,
        // they don't have a garbled aired string and their aired.from date is within the from-to parameters range.
        // Additionally, we want to include all those items which are carry overs from previous seasons.
        if ($premiered !== null) {
            $finalFilter['$or'] = [
                ['premiered' => $premiered],
                [
                    'premiered' => null,
                    'aired.string' => [
                        '$nin' => ["{$from->year} to ?"]
                    ],
                    ...$airedFilter
                ],
                // this condition will include "continuing" items from previous seasons
                [
                    'aired.from' => ['$lte' => $from->toAtomString()],
                    'airing' => true
                ]
            ];
        } else {
            $finalFilter = array_merge($finalFilter, $airedFilter);
            $finalFilter['aired.string'] = [
                '$nin' => ["{$from->year} to ?"]
            ];
        }

        if (!is_null($type)) {
            $finalFilter['type'] = $type->label;
        }

        $queryable = $queryable->whereRaw($finalFilter);

        return $queryable->orderBy('members', 'desc');
    }

    public function getUpcomingSeasonItems(
        ?AnimeTypeEnum $type = null
    ): EloquentBuilder
    {
        $queryable = $this->queryable(true)->where("status", AnimeStatusEnum::upcoming()->label);

        if (!is_null($type)) {
            $queryable = $queryable->where("type", $type->label);
        }

        return $queryable->orderBy("members", "desc");
    }

    public function orderByScore(): EloquentBuilder|ScoutBuilder
    {
        return $this
            ->queryable()
            ->orderBy("score", "desc");
    }
}
