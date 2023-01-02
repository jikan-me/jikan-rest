<?php

namespace App\Dto;

use App\Concerns\HasRequestFingerprint;
use App\Contracts\DataRequest;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * @implements DataRequest<JsonResponse>
 */
class QueryFullAnimeCommand extends Data implements DataRequest
{
    use HasRequestFingerprint;

    #[Numeric, Required]
    public int $id;
}
