<?php

namespace Database\Factories;

use App\GenreManga;
use App\Http\QueryBuilder\MangaSearchQueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;

class MangaModelFactoryDescriptor implements MediaModelFactoryDescriptor
{

    public function activityMarkerKeyName(): string
    {
        return "published";
    }

    public function typeParamMap(): array
    {
        return MangaSearchQueryBuilder::MAP_TYPES;
    }

    public function statusParamMap(): array
    {
        return MangaSearchQueryBuilder::MAP_STATUS;
    }

    public function hasRatingParam(): bool
    {
        return false;
    }

    public function mediaName(): string
    {
        return "manga";
    }

    public function genreQueryBuilder(): Builder
    {
        return GenreManga::query();
    }

    public function genreFactory(?int $count = null, $state = []): Factory
    {
        return GenreManga::factory($count, $state);
    }
}
