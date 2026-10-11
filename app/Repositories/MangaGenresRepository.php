<?php

namespace App\Repositories;

use App\Contracts\GenreRepository;
use App\GenreManga;
use App\ExplicitGenresManga;
use App\ThemeManga;
use App\DemographicManga;
use Illuminate\Support\Collection;

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
        return ExplicitGenresManga::query()->get();
    }

    public function getThemes(): Collection
    {
        return ThemeManga::query()->get();
    }

    public function getDemographics(): Collection
    {
        return DemographicManga::query()->get();
    }

    public function all(): Collection
    {
        return $this->genres()
            ->concat($this->getExplicitItems()->all())
            ->concat($this->getThemes()->all())
            ->concat($this->getDemographics()->all());
    }
}