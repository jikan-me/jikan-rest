<?php

namespace App\Repositories;

use App\Anime;
use App\Contracts\AnimeRepository;
use App\Contracts\Repository;
use App\Enums\AnimeRatingEnum;
use App\Enums\AnimeScheduleFilterEnum;
use App\Enums\AnimeStatusEnum;
use App\Enums\AnimeTypeEnum;
use Illuminate\Contracts\Database\Query\Builder as EloquentBuilder;
use Illuminate\Support\Carbon;
use Jikan\Helper\Constants;
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

    public function getCurrentlyAiring(
        ?AnimeScheduleFilterEnum $filter = null,
        bool $kids = false,
        bool $sfw = false): EloquentBuilder
    {
        /*
         * all have status as currently airing
         * all have premiered, but they're not necessarily the current season or year
         * all have aired date, but they're not necessarily the current date/season
         */
        $queryable = $this->queryable(true)
                          ->orderBy("members")
                          ->where("type", AnimeTypeEnum::tv()->label)
                          ->where("status", AnimeStatusEnum::airing()->label);

        if ($kids) {
            $queryable = $queryable->where("demographics.mal_id", Constants::GENRE_ANIME_KIDS);
        }
        else {
            $queryable = $queryable->where("demographics.mal_id", "!=", Constants::GENRE_ANIME_KIDS);
        }

        if ($sfw) {
            $queryable = $queryable->where("demographics.mal_id", "!=", Constants::GENRE_ANIME_HENTAI);
        }

        if (!is_null($filter)) {
            if ($filter->isWeekDay()) {
                $queryable = $queryable->where("broadcast", "like", "{$filter->label}%");
            }
            else {
                $queryable = $queryable->where("broadcast", $filter->label);
            }
        }

        return $queryable;
    }

    public function getAiredBetween(Carbon $from, Carbon $to, ?AnimeTypeEnum $type = null): EloquentBuilder
    {
        $queryable = $this->queryable(true)->where("aired.from", [
            $from->toAtomString(),
            $to->modify("last day of this month")->toAtomString()
        ]);

        if (!is_null($type)) {
            $queryable = $queryable->where("type", $type->label);
        }

        return $queryable->orderBy("members", "desc");
    }

    public function getUpcomingSeasonItems(?AnimeTypeEnum $type = null): EloquentBuilder
    {
        $queryable = $this->queryable(true)->where("status", AnimeStatusEnum::upcoming()->label);

        if (!is_null($type)) {
            $queryable = $queryable->where("type", $type->label);
        }

        return $queryable->orderBy("members", "desc");
    }
}
