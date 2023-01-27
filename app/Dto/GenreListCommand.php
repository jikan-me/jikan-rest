<?php

namespace App\Dto;

use App\Enums\GenreFilterEnum;
use App\Rules\Attributes\EnumValidation;
use Spatie\LaravelData\Attributes\WithCast;
use App\Casts\EnumCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

abstract class GenreListCommand extends Data
{
    #[WithCast(EnumCast::class, GenreFilterEnum::class), EnumValidation(GenreFilterEnum::class)]
    public GenreFilterEnum|Optional $filter;
}
