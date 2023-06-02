<?php

namespace App\Dto;


use App\Casts\EnumCast;
use App\Concerns\HasRequestFingerprint;
use App\Contracts\DataRequest;
use App\Dto\Concerns\HasPageParameter;
use App\Dto\Concerns\MapsRouteParameters;
use App\Enums\SortDirection;
use App\Rules\Attributes\EnumValidation;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * @implements DataRequest<JsonResponse>
 */
abstract class QueryListOfUserCommand extends Data implements DataRequest
{
    use HasRequestFingerprint, HasPageParameter, MapsRouteParameters;

    #[Min(3)]
    public string $username;

    #[Max(255), MapOutputName("title")]
    public string|Optional $q;

    #[WithCast(EnumCast::class, SortDirection::class), EnumValidation(SortDirection::class)]
    public SortDirection|Optional $sort;
}
