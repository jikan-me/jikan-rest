<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;

interface MediaModelFactoryDescriptor
{
    public function activityMarkerKeyName(): string;

    public function activityMarkerLogicalKeyName(): string;

    public function typeParamMap(): array;

    public function statusParamMap(): array;

    public function hasRatingParam(): bool;

    public function mediaName(): string;

    public function genreQueryBuilder(): Builder;

    public function genreFactory(?int $count = null, $state = []): Factory;
}
