<?php

namespace Database\Factories;

use App\GenreAnime;
use App\Http\QueryBuilder\AnimeSearchQueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnimeModelFactoryDescriptor implements MediaModelFactoryDescriptor
{
    public function activityMarkerKeyName(): string
    {
        return "aired";
    }

    public function typeParamMap(): array
    {
        return AnimeSearchQueryBuilder::MAP_TYPES;
    }

    public function statusParamMap(): array
    {
        return AnimeSearchQueryBuilder::MAP_STATUS;
    }

    public function hasRatingParam(): bool
    {
        return true;
    }

    public function mediaName(): string
    {
        return "anime";
    }

    public function genreQueryBuilder(): Builder
    {
        return GenreAnime::query();
    }

    public function genreFactory(?int $count = null, $state = []): Factory
    {
        return GenreAnime::factory($count, $state);
    }
}
