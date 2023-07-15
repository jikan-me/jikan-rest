<?php

namespace App\Dto;

use App\Casts\ContextualBooleanCast;
use App\Casts\EnumCast;
use App\Dto\Concerns\HasPreliminaryParameter;
use App\Dto\Concerns\HasSpoilersParameter;
use App\Dto\Concerns\PreparesData;
use App\Enums\MediaReviewsSortEnum;
use App\Rules\Attributes\EnumValidation;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Optional;


/**
 * @extends LookupDataCommand<JsonResponse>
 */
final class AnimeReviewsLookupCommand extends LookupDataCommand
{
    use PreparesData, HasPreliminaryParameter, HasSpoilersParameter;

    #[Numeric, Min(1)]
    public int|Optional $page = 1;

    #[WithCast(EnumCast::class, MediaReviewsSortEnum::class), EnumValidation(MediaReviewsSortEnum::class)]
    public MediaReviewsSortEnum|Optional $sort;
}
