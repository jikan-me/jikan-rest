<?php

namespace App\Repositories;

use App\Contracts\GenreRepository;
use App\DemographicAnime;
use App\ExplicitGenresAnime;
use App\GenreAnime;
use App\ThemeAnime;
use Illuminate\Support\Collection;

final class AnimeGenresRepository extends DatabaseRepository implements GenreRepository
{
    public function __construct()
    {
        parent::__construct(fn () => GenreAnime::query(), fn ($x, $y) => GenreAnime::search($x, $y));
    }

    public function genres(): Collection
    {
        return $this->queryable()->get();
    }

    public function getExplicitItems(): Collection
    {
        return ExplicitGenresAnime::query()->get();
    }

    public function getThemes(): Collection
    {
        return ThemeAnime::query()->get();
    }

    public function getDemographics(): Collection
    {
        return DemographicAnime::query()->get();
    }

    public function all(): Collection
    {
        return $this->genres()
            ->concat($this->getExplicitItems()->all())
            ->concat($this->getThemes()->all())
            ->concat($this->getDemographics()->all());
    }
}
