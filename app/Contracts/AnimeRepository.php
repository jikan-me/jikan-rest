<?php

namespace App\Contracts;

use App\Anime;
use App\Enums\AnimeScheduleFilterEnum;
use App\Enums\AnimeTypeEnum;
use Illuminate\Contracts\Database\Query\Builder as EloquentBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder as ScoutBuilder;

/**
 * @implements Repository<Anime>
 */
interface AnimeRepository extends Repository
{
    public function getTopAiringItems(): EloquentBuilder|ScoutBuilder;

    public function getTopUpcomingItems(): EloquentBuilder|ScoutBuilder;

    public function exceptItemsWithAdultRating(): EloquentBuilder|ScoutBuilder;

    public function excludeKidsItems($builder): Collection|EloquentBuilder|ScoutBuilder;

    public function excludeNsfwItems($builder): Collection|EloquentBuilder|ScoutBuilder;

    public function excludeUnapprovedItems($builder): Collection|EloquentBuilder|ScoutBuilder;

    public function orderByPopularity(): EloquentBuilder|ScoutBuilder;

    public function orderByFavoriteCount(): EloquentBuilder|ScoutBuilder;

    public function orderByRank(): EloquentBuilder|ScoutBuilder;

    public function getCurrentlyAiring(
        ?AnimeScheduleFilterEnum $filter = null
    ): EloquentBuilder;

    public function getAiredBetween(
        Carbon $from,
        Carbon $to,
        ?AnimeTypeEnum $type = null
    ): EloquentBuilder;

    public function getUpcomingSeasonItems(?AnimeTypeEnum $type = null): EloquentBuilder;
}
