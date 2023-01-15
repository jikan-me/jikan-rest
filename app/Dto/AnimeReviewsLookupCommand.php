<?php

namespace App\Dto;

use App\Casts\EnumCast;
use App\Enums\AnimeReviewsSortEnum;
use Illuminate\Http\JsonResponse;
use Spatie\Enum\Laravel\Rules\EnumRule;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Optional;

/**
 * @extends LookupDataCommand<JsonResponse>
 */
final class AnimeReviewsLookupCommand extends LookupDataCommand
{
    #[Numeric]
    public int|Optional $page;

    #[WithCast(EnumCast::class, AnimeReviewsSortEnum::class)]
    public AnimeReviewsSortEnum|Optional $sort;

    #[BooleanType]
    public bool|Optional $spoilers;

    #[BooleanType]
    public bool|Optional $preliminary;

    public static function rules(): array
    {
        return [
            "sort" => [new EnumRule(AnimeReviewsSortEnum::class)]
        ];
    }
}
