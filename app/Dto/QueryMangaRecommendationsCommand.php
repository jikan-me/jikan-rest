<?php

namespace App\Dto;


use App\Concerns\HasRequestFingerprint;
use App\Contracts\DataRequest;
use App\Http\Resources\V4\ResultsResource;
use Spatie\LaravelData\Data;

/**
 * @implements DataRequest<ResultsResource>
 */
final class QueryMangaRecommendationsCommand extends Data implements DataRequest
{
    use HasRequestFingerprint;
}
