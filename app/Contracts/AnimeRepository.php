<?php

namespace App\Contracts;

use App\Anime;
use App\Enums\AnimeScheduleFilterEnum;
use App\Enums\AnimeTypeEnum;
use Illuminate\Contracts\Database\Query\Builder as EloquentBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder;
use Laravel\Scout\Builder as ScoutBuilder;

/**
 * @extends Repository<Anime>
 */
interface AnimeRepository extends Repository
{
    /**
     * @return EloquentBuilder|Builder<Anime>
     */
    public function getTopAiringItems(): EloquentBuilder|ScoutBuilder;

    /**
     * @return EloquentBuilder|Builder<Anime>
     */
    public function getTopUpcomingItems(): EloquentBuilder|ScoutBuilder;

    /**
     * @return EloquentBuilder|Builder<Anime>
     */
    public function exceptItemsWithAdultRating(): EloquentBuilder|ScoutBuilder;

    /**
     * @return EloquentBuilder|Builder<Anime>
     */
    public function excludeKidsItems($builder): Collection|EloquentBuilder|ScoutBuilder;

    /**
     * @return EloquentBuilder|Builder<Anime>
     */
    public function excludeNsfwItems($builder): Collection|EloquentBuilder|ScoutBuilder;

    /**
     * @return EloquentBuilder|Builder<Anime>
     */
    public function excludeUnapprovedItems($builder): Collection|EloquentBuilder|ScoutBuilder;

    /**
     * @return EloquentBuilder|Builder<Anime>
     */
    public function orderByPopularity(): EloquentBuilder|ScoutBuilder;

    /**
     * @return EloquentBuilder|Builder<Anime>
     */
    public function orderByFavoriteCount(): EloquentBuilder|ScoutBuilder;

    /**
     * @return EloquentBuilder|Builder<Anime>
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
     * @return EloquentBuilder|Builder<Anime>
     */
    public function orderByScore(): EloquentBuilder|ScoutBuilder;
}
