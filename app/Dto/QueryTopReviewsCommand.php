<?php

namespace App\Dto;

use App\Casts\ContextualBooleanCast;
use App\Casts\EnumCast;
use App\Concerns\HasRequestFingerprint;
use App\Contracts\DataRequest;
use App\Dto\Concerns\HasPreliminaryParameter;
use App\Dto\Concerns\HasSpoilersParameter;
use App\Dto\Concerns\PreparesData;
use App\Enums\TopAnimeFilterEnum;
use App\Enums\TopReviewsTypeEnum;
use App\Rules\Attributes\EnumValidation;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Optional;

/**
 * @implements DataRequest<JsonResponse>
 */
final class QueryTopReviewsCommand extends QueryTopItemsCommand implements DataRequest
{
    use HasRequestFingerprint, HasPreliminaryParameter, HasSpoilersParameter, PreparesData;

    #[WithCast(EnumCast::class, TopAnimeFilterEnum::class), EnumValidation(TopReviewsTypeEnum::class)]
    public TopReviewsTypeEnum|Optional $type;
}
