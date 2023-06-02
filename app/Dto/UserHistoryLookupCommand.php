<?php

namespace App\Dto;


use App\Casts\EnumCast;
use App\Enums\UserHistoryTypeEnum;
use App\Rules\Attributes\EnumValidation;
use Illuminate\Http\JsonResponse;
use Spatie\Enum\Laravel\Rules\EnumRule;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\WithCast;

/**
 * @extends LookupDataCommand<JsonResponse>
 */
final class UserHistoryLookupCommand extends LookupByUsernameCommand
{
    #[WithCast(EnumCast::class, UserHistoryTypeEnum::class), EnumValidation(UserHistoryTypeEnum::class)]
    public ?UserHistoryTypeEnum $type;
}
