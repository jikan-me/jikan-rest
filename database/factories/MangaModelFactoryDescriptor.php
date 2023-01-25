<?php

namespace Database\Factories;

use App\Enums\MangaStatusEnum;
use App\Enums\MangaTypeEnum;
use App\GenreManga;
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
        return MangaTypeEnum::toArray();
    }

    public function statusParamMap(): array
    {
        return MangaStatusEnum::toArray();
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

    public function activityMarkerLogicalKeyName(): string
    {
        return "publishing";
    }
}
