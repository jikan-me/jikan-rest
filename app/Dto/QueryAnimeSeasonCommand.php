<?php

namespace App\Dto;


use App\Casts\EnumCast;
use App\Concerns\HasRequestFingerprint;
use App\Contracts\DataRequest;
use App\Dto\Concerns\HasLimitParameter;
use App\Dto\Concerns\HasPageParameter;
use App\Enums\AnimeTypeEnum;
use App\Rules\Attributes\EnumValidation;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;


abstract class QueryAnimeSeasonCommand extends Data implements DataRequest
{
    use HasLimitParameter, HasRequestFingerprint, HasPageParameter;

    #[WithCast(EnumCast::class, AnimeTypeEnum::class), EnumValidation(AnimeTypeEnum::class)]
    public AnimeTypeEnum|Optional $filter;
}
