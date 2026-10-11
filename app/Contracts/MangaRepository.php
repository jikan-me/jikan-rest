<?php

namespace App\Contracts;

use App\Manga;
use Illuminate\Contracts\Database\Query\Builder as EloquentBuilder;
use Laravel\Scout\Builder as ScoutBuilder;

/**
 * @extends Repository<Manga>
 */
interface MangaRepository extends Repository
{
    /**
     * @return \Illuminate\Contracts\Database\Query\Builder|\Laravel\Scout\Builder<\App\Manga>
     */
    public function getTopPublishingItems(): EloquentBuilder|ScoutBuilder;

    /**
     * @return \Illuminate\Contracts\Database\Query\Builder|\Laravel\Scout\Builder<\App\Manga>
     */
    public function getTopUpcomingItems(): EloquentBuilder|ScoutBuilder;

    /**
     * @return \Illuminate\Contracts\Database\Query\Builder|\Laravel\Scout\Builder<\App\Manga>
     */
    public function orderByPopularity(): EloquentBuilder|ScoutBuilder;

    /**
     * @return \Illuminate\Contracts\Database\Query\Builder|\Laravel\Scout\Builder<\App\Manga>
     */
    public function orderByFavoriteCount(): EloquentBuilder|ScoutBuilder;

    /**
     * @return \Illuminate\Contracts\Database\Query\Builder|\Laravel\Scout\Builder<\App\Manga>
     */
    public function orderByRank(): EloquentBuilder|ScoutBuilder;

    /**
     * @return \Illuminate\Contracts\Database\Query\Builder|\Laravel\Scout\Builder<\App\Manga>
     */
    public function exceptItemsWithAdultRating(): EloquentBuilder|ScoutBuilder;

    /**
     * @return \Illuminate\Contracts\Database\Query\Builder|\Laravel\Scout\Builder<\App\Manga>
     */
    public function orderByScore(): EloquentBuilder|ScoutBuilder;

}
