<?php

namespace App\Dto;


use App\Casts\EnumCast;
use App\Enums\UserHistoryTypeEnum;
use App\Rules\Attributes\EnumValidation;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Attributes\WithCast;

/**
 * @extends LookupByUsernameCommand<JsonResponse>

 */
final class UserHistoryLookupCommand extends LookupByUsernameCommand
{
    #[WithCast(EnumCast::class, UserHistoryTypeEnum::class), EnumValidation(UserHistoryTypeEnum::class)]
    public ?UserHistoryTypeEnum $type;
}
