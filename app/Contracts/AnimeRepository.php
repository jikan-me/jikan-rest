<?php

namespace App\Contracts;

use App\Anime;
use App\Enums\AnimeScheduleFilterEnum;
use App\Enums\AnimeTypeEnum;
use Illuminate\Contracts\Database\Query\Builder as EloquentBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder as ScoutBuilder;
use Laravel\Scout\Scout;

/**
 * @extends Repository<Anime>
 */
interface AnimeRepository extends Repository
{
    /**
     * @return \Illuminate\Contracts\Database\Query\Builder|\Laravel\Scout\Builder<\App\Anime>
     */
    public function getTopAiringItems(): EloquentBuilder|ScoutBuilder;

    /**
     * @return \Illuminate\Contracts\Database\Query\Builder|\Laravel\Scout\Builder<\App\Anime>
     */
    public function getTopUpcomingItems(): EloquentBuilder|ScoutBuilder;

    /**
     * @return \Illuminate\Contracts\Database\Query\Builder|\Laravel\Scout\Builder<\App\Anime>
     */
    public function exceptItemsWithAdultRating(): EloquentBuilder|ScoutBuilder;

    /**
     * @return \Illuminate\Contracts\Database\Query\Builder|\Laravel\Scout\Builder<\App\Anime>
     */
    public function excludeKidsItems($builder): Collection|EloquentBuilder|ScoutBuilder;

    /**
     * @return \Illuminate\Contracts\Database\Query\Builder|\Laravel\Scout\Builder<\App\Anime>
     */
    public function excludeNsfwItems($builder): Collection|EloquentBuilder|ScoutBuilder;

    /**
     * @return \Illuminate\Contracts\Database\Query\Builder|\Laravel\Scout\Builder<\App\Anime>
     */
    public function excludeUnapprovedItems($builder): Collection|EloquentBuilder|ScoutBuilder;

    /**
     * @return \Illuminate\Contracts\Database\Query\Builder|\Laravel\Scout\Builder<\App\Anime>
     */
    public function orderByPopularity(): EloquentBuilder|ScoutBuilder;

    /**
     * @return \Illuminate\Contracts\Database\Query\Builder|\Laravel\Scout\Builder<\App\Anime>
     */
    public function orderByFavoriteCount(): EloquentBuilder|ScoutBuilder;

    /**
     * @return \Illuminate\Contracts\Database\Query\Builder|\Laravel\Scout\Builder<\App\Anime>
     */
    public function orderByRank(): EloquentBuilder|ScoutBuilder;

    public function getCurrentlyAiring(
        ?AnimeScheduleFilterEnum $filter = null
    ): EloquentBuilder;

    public function getItemsBySeason(
        Carbon $from,
        Carbon $to,
        ?AnimeTypeEnum $type = null,
        ?string $premiered = null,
        bool $includeContinuingItems = false
    ): EloquentBuilder;

    public function getUpcomingSeasonItems(?AnimeTypeEnum $type = null): EloquentBuilder;

    /**
     * @return \Illuminate\Contracts\Database\Query\Builder|\Laravel\Scout\Builder<\App\Anime>
     */
    public function orderByScore(): EloquentBuilder|ScoutBuilder;
}
