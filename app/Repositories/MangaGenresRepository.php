<?php

namespace App\Repositories;

use App\Contracts\GenreRepository;
use App\GenreManga;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Builder as ScoutBuilder;

final class MangaGenresRepository extends DatabaseRepository implements GenreRepository
{
    public function __construct()
    {
        parent::__construct(fn () => GenreManga::query(), fn ($x, $y) => GenreManga::search($x, $y));
    }

    public function genres(): Collection
    {
        return $this->queryable()->get();
    }

    public function getExplicitItems(): Collection
    {
        return DB::table('explicit_genres_manga')->get();
    }

    public function getThemes(): Collection
    {
        return DB::table('themes_manga')->get();
    }

    public function getDemographics(): Collection
    {
        return DB::table('demographics_manga')->get();
    }

    public function all(): Collection
    {
        return $this->genres()
            ->concat($this->getExplicitItems()->all())
            ->concat($this->getThemes()->all())
            ->concat($this->getDemographics()->all());
    }
}
