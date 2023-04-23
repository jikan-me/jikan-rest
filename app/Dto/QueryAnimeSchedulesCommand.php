<?php

namespace App\Dto;


use App\Casts\ContextualBooleanCast;
use App\Casts\EnumCast;
use App\Concerns\HasRequestFingerprint;
use App\Contracts\DataRequest;
use App\Dto\Concerns\HasKidsParameter;
use App\Dto\Concerns\HasLimitParameter;
use App\Dto\Concerns\HasPageParameter;
use App\Dto\Concerns\HasSfwParameter;
use App\Dto\Concerns\HasUnapprovedParameter;
use App\Dto\Concerns\MapsRouteParameters;
use App\Dto\Concerns\PreparesData;
use App\Enums\AnimeScheduleFilterEnum;
use App\Rules\Attributes\EnumValidation;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * @implements DataRequest<JsonResponse>
 */
final class QueryAnimeSchedulesCommand extends Data implements DataRequest
{
    use HasLimitParameter, HasRequestFingerprint, HasPageParameter, PreparesData, HasSfwParameter, HasKidsParameter, HasUnapprovedParameter, MapsRouteParameters;

    #[WithCast(EnumCast::class, AnimeScheduleFilterEnum::class), EnumValidation(AnimeScheduleFilterEnum::class)]
    public ?AnimeScheduleFilterEnum $dayFilter;
}
