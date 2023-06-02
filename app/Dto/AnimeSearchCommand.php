<?php

namespace App\Dto;

use App\Contracts\DataRequest;
use App\Dto\Concerns\HasSfwParameter;
use App\Enums\AnimeOrderByEnum;
use App\Enums\AnimeRatingEnum;
use App\Enums\AnimeStatusEnum;
use App\Enums\AnimeTypeEnum;
use App\Http\Resources\V4\AnimeCollection;
use App\Rules\Attributes\EnumValidation;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Prohibits;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\WithCast;
use App\Casts\EnumCast;
use Spatie\LaravelData\Optional;

/**
 * @implements DataRequest<AnimeCollection>
 */
final class AnimeSearchCommand extends MediaSearchCommand implements DataRequest
{
    #[WithCast(EnumCast::class, AnimeStatusEnum::class), EnumValidation(AnimeStatusEnum::class)]
    public AnimeStatusEnum|Optional $status;

    #[WithCast(EnumCast::class, AnimeTypeEnum::class), EnumValidation(AnimeTypeEnum::class)]
    public AnimeTypeEnum|Optional $type;

    #[WithCast(EnumCast::class, AnimeRatingEnum::class), EnumValidation(AnimeRatingEnum::class)]
    public AnimeRatingEnum|Optional $rating;

    #[IntegerType, Min(1)]
    public int|Optional $producer;

    #[Prohibits("producer"), StringType]
    public string|Optional $producers;

    #[
        MapInputName("order_by"),
        MapOutputName("order_by"),
        WithCast(EnumCast::class, AnimeOrderByEnum::class),
        EnumValidation(AnimeOrderByEnum::class)
    ]
    public AnimeOrderByEnum|Optional $orderBy;
}
