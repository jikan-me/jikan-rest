<?php

namespace App\Repositories;

use App\Contracts\MangaRepository;
use App\Enums\MangaStatusEnum;
use App\Enums\MangaTypeEnum;
use App\Manga;
use Illuminate\Contracts\Database\Query\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Jikan\Helper\Constants;
use Laravel\Scout\Builder as ScoutBuilder;

final class DefaultMangaRepository extends DatabaseRepository implements MangaRepository
{
    public function __construct()
    {
        parent::__construct(fn () => Manga::query(), fn ($x, $y) => Manga::search($x, $y));
    }

    public function getTopPublishingItems(): EloquentBuilder|ScoutBuilder
    {
        return $this->orderByRank()
            ->where("publishing", true);
    }

    public function getTopUpcomingItems(): EloquentBuilder|ScoutBuilder
    {
        return $this->orderByRank()
            ->where("status", MangaStatusEnum::upcoming()->label);
    }

    public function orderByPopularity(): EloquentBuilder|ScoutBuilder
    {
        return $this->queryable()->orderBy("members", "desc");
    }

    public function orderByFavoriteCount(): EloquentBuilder|ScoutBuilder
    {
        return $this->queryable()->orderBy("favorites", "desc");
    }

    public function orderByRank(): EloquentBuilder|ScoutBuilder
    {
        return $this->queryable()
            ->whereNotNull("rank")
            ->where("rank", ">", 0)
            ->orderBy("rank");
    }

    public function exceptItemsWithAdultRating(): EloquentBuilder|ScoutBuilder
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->queryable()->exceptItemsWithAdultRating();
    }
}
