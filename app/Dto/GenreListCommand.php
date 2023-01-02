<?php

namespace App\Dto;

use App\Enums\GenreFilterEnum;
use Spatie\Enum\Laravel\Rules\EnumRule;
use Spatie\LaravelData\Attributes\WithCast;
use App\Casts\EnumCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

abstract class GenreListCommand extends Data
{
    #[WithCast(EnumCast::class, GenreFilterEnum::class)]
    public GenreFilterEnum|Optional $filter;

    public static function rules(): array
    {
        return [
            "filter" => [new EnumRule(GenreFilterEnum::class)]
        ];
    }
}
