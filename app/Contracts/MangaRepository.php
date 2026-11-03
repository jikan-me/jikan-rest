<?php

namespace App\Contracts;

use App\Manga;
use Illuminate\Contracts\Database\Query\Builder as EloquentBuilder;
use Laravel\Scout\Builder;
use Laravel\Scout\Builder as ScoutBuilder;

/**
 * @extends Repository<Manga>
 */
interface MangaRepository extends Repository
{
    /**
     * @return EloquentBuilder|Builder<Manga>
     */
    public function getTopPublishingItems(): EloquentBuilder|ScoutBuilder;

    /**
     * @return EloquentBuilder|Builder<Manga>
     */
    public function getTopUpcomingItems(): EloquentBuilder|ScoutBuilder;

    /**
     * @return EloquentBuilder|Builder<Manga>
     */
    public function orderByPopularity(): EloquentBuilder|ScoutBuilder;

    /**
     * @return EloquentBuilder|Builder<Manga>
     */
    public function orderByFavoriteCount(): EloquentBuilder|ScoutBuilder;

    /**
     * @return EloquentBuilder|Builder<Manga>
     */
    public function orderByRank(): EloquentBuilder|ScoutBuilder;

    /**
     * @return EloquentBuilder|Builder<Manga>
     */
    public function exceptItemsWithAdultRating(): EloquentBuilder|ScoutBuilder;

    /**
     * @return EloquentBuilder|Builder<Manga>
     */
    public function orderByScore(): EloquentBuilder|ScoutBuilder;
}
