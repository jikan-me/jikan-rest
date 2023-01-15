<?php

namespace App\Dto;

use App\Concerns\HasRequestFingerprint;
use App\Contracts\DataRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * Base class for all requests/commands which are for looking up things by id.
 * @template T of ResourceCollection|JsonResource|Response
 * @implements DataRequest<T>
 */
abstract class LookupDataCommand extends Data implements DataRequest
{
    use HasRequestFingerprint;

    #[Numeric, Required]
    public int $id;

    /** @noinspection PhpUnused */
    public static function fromRequestAndKey(Request $request, int $id): self
    {
        $data = static::fromRequest($request);
        $data->id = $id;
        return $data;
    }
}
