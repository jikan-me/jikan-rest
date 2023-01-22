<?php

namespace App\Dto;


use App\Casts\EnumCast;
use App\Concerns\HasRequestFingerprint;
use App\Contracts\DataRequest;
use App\Dto\Concerns\MapsDefaultLimitParameter;
use App\Enums\AnimeTypeEnum;
use App\Rules\Attributes\MaxLimitWithFallback;
use Spatie\Enum\Laravel\Rules\EnumRule;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;


abstract class QueryAnimeSeasonCommand extends Data implements DataRequest
{
    use MapsDefaultLimitParameter, HasRequestFingerprint;

    #[WithCast(EnumCast::class, AnimeTypeEnum::class)]
    public AnimeTypeEnum|Optional $filter;

    #[Numeric, Min(1)]
    public int|Optional $page = 1;

    #[Numeric, Min(1), MaxLimitWithFallback]
    public int|Optional $limit;


    public static function rules(...$args): array
    {
        return [
            "filter" => [new EnumRule(AnimeTypeEnum::class)]
        ];
    }
}
