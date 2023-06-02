<?php

namespace App\Dto;

use App\Casts\EnumCast;
use App\Contracts\DataRequest;
use App\Enums\MangaOrderByEnum;
use App\Enums\MangaStatusEnum;
use App\Enums\MangaTypeEnum;
use App\Http\Resources\V4\MangaCollection;
use App\Rules\Attributes\EnumValidation;
use Spatie\Enum\Laravel\Rules\EnumRule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Optional;

/**
 * @implements DataRequest<MangaCollection>
 */
final class MangaSearchCommand extends MediaSearchCommand implements DataRequest
{
    #[WithCast(EnumCast::class, MangaStatusEnum::class), EnumValidation(MangaStatusEnum::class)]
    public MangaStatusEnum|Optional $status;

    #[WithCast(EnumCast::class, MangaTypeEnum::class), EnumValidation(MangaTypeEnum::class)]
    public MangaTypeEnum|Optional $type;

    #[StringType]
    public string|Optional $magazines;

    #[
        MapInputName("order_by"),
        MapOutputName("order_by"),
        EnumValidation(MangaOrderByEnum::class),
        WithCast(EnumCast::class, MangaOrderByEnum::class)
    ]
    public MangaOrderByEnum|Optional $orderBy;
}
