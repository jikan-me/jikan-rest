<?php

namespace App\Dto;

use App\Concerns\HasRequestFingerprint;
use App\Contracts\DataRequest;
use Illuminate\Http\JsonResponse;

/**
 * @implements DataRequest<JsonResponse>
 */
final class QueryTopReviewsCommand extends QueryTopItemsCommand implements DataRequest
{
    use HasRequestFingerprint;
}
