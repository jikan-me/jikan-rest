<?php

namespace App\Dto;


use App\Casts\EnumCast;
use App\Concerns\HasRequestFingerprint;
use App\Contracts\DataRequest;
use App\Dto\Concerns\HasLimitParameter;
use App\Dto\Concerns\HasPageParameter;
use App\Enums\AnimeTypeEnum;
use Spatie\Enum\Laravel\Rules\EnumRule;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;


abstract class QueryAnimeSeasonCommand extends Data implements DataRequest
{
    use HasLimitParameter, HasRequestFingerprint, HasPageParameter;

    #[WithCast(EnumCast::class, AnimeTypeEnum::class)]
    public AnimeTypeEnum|Optional $filter;

    public static function rules(...$args): array
    {
        return [
            "filter" => [new EnumRule(AnimeTypeEnum::class)]
        ];
    }
}
