<?php

namespace App\Contracts;

use App\Anime;
use \Illuminate\Database\Eloquent\Builder as EloquentBuilder;
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
}
