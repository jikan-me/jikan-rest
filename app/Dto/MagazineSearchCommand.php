<?php

namespace App\Dto;

use App\Casts\EnumCast;
use App\Contracts\DataRequest;
use App\Enums\MagazineOrderByEnum;
use App\Http\Resources\V4\MagazineCollection;
use App\Rules\Attributes\EnumValidation;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Optional;

/**
 * @implements DataRequest<MagazineCollection>
 */
final class MagazineSearchCommand extends SearchCommand implements DataRequest
{
    #[MapInputName("order_by"), MapOutputName("order_by"),
        WithCast(EnumCast::class, MagazineOrderByEnum::class), EnumValidation(MagazineOrderByEnum::class)]
    public MagazineOrderByEnum|Optional $orderBy;
}
