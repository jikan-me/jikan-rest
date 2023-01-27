<?php

namespace App\Dto;

use App\Casts\EnumCast;
use App\Contracts\DataRequest;
use App\Enums\PeopleOrderByEnum;
use App\Http\Resources\V4\PersonCollection;
use App\Rules\Attributes\EnumValidation;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Optional;

/**
 * @implements DataRequest<PersonCollection>
 */
final class PeopleSearchCommand extends SearchCommand implements DataRequest
{
    #[MapInputName("order_by"), MapOutputName("order_by"),
        WithCast(EnumCast::class, PeopleOrderByEnum::class), EnumValidation(PeopleOrderByEnum::class)]
    public PeopleOrderByEnum|Optional $orderBy;
}
