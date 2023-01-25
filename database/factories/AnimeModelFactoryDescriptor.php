<?php

namespace Database\Factories;

use App\Enums\AnimeStatusEnum;
use App\Enums\AnimeTypeEnum;
use App\GenreAnime;
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
        return AnimeTypeEnum::toArray();
    }

    public function statusParamMap(): array
    {
        return AnimeStatusEnum::toArray();
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

    public function activityMarkerLogicalKeyName(): string
    {
        return "airing";
    }
}
