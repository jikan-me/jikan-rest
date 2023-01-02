<?php

namespace App\Repositories;

use App\Anime;
use App\Contracts\AnimeRepository;
use App\Contracts\Repository;
use App\Enums\AnimeRatingEnum;
use App\Enums\AnimeStatusEnum;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
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
        return $this->exceptItemsWithAdultRating()
            ->where("airing", true)
            ->whereNotNull("rank")
            ->where("rank", ">", 0)
            ->orderBy("rank");
    }

    public function getTopUpcomingItems(): EloquentBuilder|ScoutBuilder
    {
        return $this->exceptItemsWithAdultRating()
            ->where("status", AnimeStatusEnum::upcoming()->label)
            ->whereNotNull("rank")
            ->where("rank", ">", 0)
            ->orderBy("rank");
    }

    public function exceptItemsWithAdultRating(): EloquentBuilder|ScoutBuilder
    {
        return $this->queryable()->where("rating", "!=", AnimeRatingEnum::rx()->label);
    }

    public function orderByPopularity(): EloquentBuilder|ScoutBuilder
    {
        return $this->exceptItemsWithAdultRating()->orderBy("members", "desc");
    }

    public function orderByFavoriteCount(): EloquentBuilder|ScoutBuilder
    {
        return $this->exceptItemsWithAdultRating()->orderBy("favorites", "desc");
    }

    public function orderByRank(): EloquentBuilder|ScoutBuilder
    {
        return $this->exceptItemsWithAdultRating()
            ->whereNotNull("rank")
            ->where("rank", ">", 0)
            ->orderBy("rank");
    }
}
