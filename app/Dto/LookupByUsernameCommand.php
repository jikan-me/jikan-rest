<?php

namespace App\Dto;

use App\Concerns\HasRequestFingerprint;
use App\Contracts\DataRequest;
use App\Dto\Concerns\MapsRouteParameters;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

/**
 * Base class for all requests/commands which are for looking up things by username.
 * @template T of ResourceCollection|JsonResource|Response
 * @implements DataRequest<T>
 */
abstract class LookupByUsernameCommand extends Data implements DataRequest
{
    use MapsRouteParameters, HasRequestFingerprint;

    #[StringType, Max(255), Min(3)]
    public string $username;
}
