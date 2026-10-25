<?php

namespace App\Dto;

use App\Casts\EnumCast;
use App\Concerns\HasRequestFingerprint;
use App\Contracts\DataRequest;
use App\Dto\Concerns\HasContinuingParameter;
use App\Dto\Concerns\HasKidsParameter;
use App\Dto\Concerns\HasLimitParameter;
use App\Dto\Concerns\HasPageParameter;
use App\Dto\Concerns\HasSfwParameter;
use App\Dto\Concerns\HasUnapprovedParameter;
use App\Enums\AnimeTypeEnum;
use App\Rules\Attributes\EnumValidation;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * @implements DataRequest<JsonResponse>
 */
abstract class QueryAnimeSeasonCommand extends Data implements DataRequest
{
    use HasContinuingParameter,
        HasKidsParameter,
        HasLimitParameter,
        HasPageParameter,
        HasRequestFingerprint,
        HasSfwParameter,
        HasUnapprovedParameter;

    #[WithCast(EnumCast::class, AnimeTypeEnum::class), EnumValidation(AnimeTypeEnum::class)]
    public AnimeTypeEnum|Optional $filter;
}
