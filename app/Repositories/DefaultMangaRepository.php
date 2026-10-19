<?php

namespace App\Repositories;

use App\Contracts\MangaRepository;
use App\Enums\MangaStatusEnum;
use App\Manga;
use Illuminate\Contracts\Database\Query\Builder as EloquentBuilder;
use Laravel\Scout\Builder;
use Laravel\Scout\Builder as ScoutBuilder;

final class DefaultMangaRepository extends DatabaseRepository implements MangaRepository
{
    public function __construct()
    {
        parent::__construct(fn () => Manga::query(), fn ($x, $y) => Manga::search($x, $y));
    }

    /**
     * @return EloquentBuilder|Builder<Manga>
     */
    public function getTopPublishingItems(): EloquentBuilder|ScoutBuilder
    {
        return $this
            ->orderByPopularity()
            ->where('publishing', true);
    }

    /**
     * @return EloquentBuilder|Builder<Manga>
     */
    public function getTopUpcomingItems(): EloquentBuilder|ScoutBuilder
    {
        return $this
            ->orderByPopularity()
            ->where('status', MangaStatusEnum::upcoming()->label);
    }

    /**
     * @return EloquentBuilder|Builder<Manga>
     */
    public function orderByPopularity(): EloquentBuilder|ScoutBuilder
    {
        return $this
            ->queryable()
            ->orderBy('members', 'desc');
    }

    /**
     * @return EloquentBuilder|Builder<Manga>
     */
    public function orderByFavoriteCount(): EloquentBuilder|ScoutBuilder
    {
        return $this->queryable()->orderBy('favorites', 'desc');
    }

    /**
     * @return EloquentBuilder|Builder<Manga>
     */
    public function orderByRank(): EloquentBuilder|ScoutBuilder
    {
        return $this->queryable()
            ->whereNotNull('rank')
            ->where('rank', '>', 0)
            ->orderBy('rank');
    }

    /**
     * @return EloquentBuilder|Builder<Manga>
     */
    public function exceptItemsWithAdultRating(): EloquentBuilder|ScoutBuilder
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->queryable()->exceptItemsWithAdultRating();
    }

    /**
     * @return EloquentBuilder|Builder<Manga>
     */
    public function orderByScore(): EloquentBuilder|ScoutBuilder
    {
        return $this
            ->queryable()
            ->orderBy('score', 'desc');
    }
}
