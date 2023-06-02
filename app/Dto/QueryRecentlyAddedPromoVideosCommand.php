<?php

namespace App\Dto;


use App\Concerns\HasRequestFingerprint;
use App\Contracts\DataRequest;
use App\Dto\Concerns\HasPageParameter;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Data;

/**
 * @implements DataRequest<JsonResponse>
 */
final class QueryRecentlyAddedPromoVideosCommand extends Data implements DataRequest
{
    use HasRequestFingerprint, HasPageParameter;
}
