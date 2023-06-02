<?php

namespace App\Dto;

use App\Casts\EnumCast;
use App\Contracts\DataRequest;
use App\Enums\ClubCategoryEnum;
use App\Enums\ClubOrderByEnum;
use App\Enums\ClubTypeEnum;
use App\Http\Resources\V4\ClubCollection;
use App\Rules\Attributes\EnumValidation;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Optional;

/**
 * @implements DataRequest<ClubCollection>
 */
final class ClubSearchCommand extends SearchCommand implements DataRequest
{
    #[WithCast(EnumCast::class, ClubCategoryEnum::class), EnumValidation(ClubCategoryEnum::class)]
    public ClubCategoryEnum|Optional $category;

    #[WithCast(EnumCast::class, ClubTypeEnum::class), EnumValidation(ClubTypeEnum::class)]
    public ClubTypeEnum|Optional $type;

    #[MapInputName("order_by"), MapOutputName("order_by"),
        WithCast(EnumCast::class, ClubOrderByEnum::class), EnumValidation(ClubOrderByEnum::class)]
    public ClubOrderByEnum|Optional $orderBy;
}
