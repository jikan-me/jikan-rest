<?php

namespace App\Dto;


use App\Casts\EnumCast;
use App\Concerns\HasRequestFingerprint;
use App\Contracts\DataRequest;
use App\Dto\Concerns\HasPageParameter;
use App\Enums\MediaReviewsSortEnum;
use App\Http\Resources\V4\ResultsResource;
use App\Rules\Attributes\EnumValidation;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * @implements DataRequest<ResultsResource>
 */
abstract class QueryReviewsCommand extends Data implements DataRequest
{
    use HasRequestFingerprint, HasPageParameter;

    #[WithCast(EnumCast::class, MediaReviewsSortEnum::class), EnumValidation(MediaReviewsSortEnum::class)]
    public MediaReviewsSortEnum|Optional $sort;

    #[BooleanType]
    public bool|Optional $spoilers;

    #[BooleanType]
    public bool|Optional $preliminary;
}
