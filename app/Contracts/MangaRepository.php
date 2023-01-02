<?php

namespace App\Contracts;

use App\Manga;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Laravel\Scout\Builder as ScoutBuilder;

/**
 * @implements Repository<Manga>
 */
interface MangaRepository extends Repository
{
    public function getTopPublishingItems(): EloquentBuilder|ScoutBuilder;

    public function getTopUpcomingItems(): EloquentBuilder|ScoutBuilder;

    public function orderByPopularity(): EloquentBuilder|ScoutBuilder;

    public function orderByFavoriteCount(): EloquentBuilder|ScoutBuilder;

    public function orderByRank(): EloquentBuilder|ScoutBuilder;
}
