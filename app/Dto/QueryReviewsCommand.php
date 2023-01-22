<?php

namespace App\Dto;


use App\Casts\EnumCast;
use App\Concerns\HasRequestFingerprint;
use App\Contracts\DataRequest;
use App\Enums\MediaReviewsSortEnum;
use App\Http\Resources\V4\ResultsResource;
use Spatie\Enum\Laravel\Rules\EnumRule;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * @implements DataRequest<ResultsResource>
 */
abstract class QueryReviewsCommand extends Data implements DataRequest
{
    use HasRequestFingerprint;

    #[Numeric, Min(1)]
    public int|Optional $page = 1;

    #[WithCast(EnumCast::class, MediaReviewsSortEnum::class)]
    public MediaReviewsSortEnum|Optional $sort;

    #[BooleanType]
    public bool|Optional $spoilers;

    #[BooleanType]
    public bool|Optional $preliminary;

    public static function rules(): array
    {
        return [
            "sort" => [new EnumRule(MediaReviewsSortEnum::class)]
        ];
    }
}
