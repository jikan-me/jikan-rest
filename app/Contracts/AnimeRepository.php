<?php

namespace App\Contracts;

use App\Anime;
use App\Enums\AnimeScheduleFilterEnum;
use Illuminate\Contracts\Database\Query\Builder as EloquentBuilder;
use \Laravel\Scout\Builder as ScoutBuilder;

/**
 * @implements Repository<Anime>
 */
interface AnimeRepository extends Repository
{
    public function getTopAiringItems(): EloquentBuilder|ScoutBuilder;

    public function getTopUpcomingItems(): EloquentBuilder|ScoutBuilder;

    public function exceptItemsWithAdultRating(): EloquentBuilder|ScoutBuilder;

    public function orderByPopularity(): EloquentBuilder|ScoutBuilder;

    public function orderByFavoriteCount(): EloquentBuilder|ScoutBuilder;

    public function orderByRank(): EloquentBuilder|ScoutBuilder;

    public function getCurrentlyAiring(
        ?AnimeScheduleFilterEnum $filter = null,
        bool $kids = false,
        bool $sfw = false
    ): EloquentBuilder;
}
