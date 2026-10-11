<?php

namespace App\Dto;

use App\Contracts\DataRequest;
use App\Enums\GenreFilterEnum;
use App\Http\Resources\V4\GenreCollection;
use App\Rules\Attributes\EnumValidation;
use Spatie\LaravelData\Attributes\WithCast;
use App\Casts\EnumCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * @implements DataRequest<GenreCollection>
 */
abstract class GenreListCommand extends Data implements DataRequest
{
    #[WithCast(EnumCast::class, GenreFilterEnum::class), EnumValidation(GenreFilterEnum::class)]
    public GenreFilterEnum|Optional $filter;
}
