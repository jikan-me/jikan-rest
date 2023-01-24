<?php

namespace App\Dto;

use App\Casts\EnumCast;
use App\Concerns\HasRequestFingerprint;
use App\Contracts\DataRequest;
use App\Enums\GenderEnum;
use App\Http\Resources\V4\UserCollection;
use Spatie\Enum\Laravel\Rules\EnumRule;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Optional;

/**
 * @implements DataRequest<UserCollection>
 */
final class UsersSearchCommand extends SearchCommand implements DataRequest
{
    use HasRequestFingerprint;

    public int|Optional $minAge;

    public int|Optional $maxAge;

    #[WithCast(EnumCast::class, GenderEnum::class)]
    public GenderEnum|Optional $gender;

    #[StringType]
    public string|Optional $location;

    public static function rules(): array
    {
        return [
            ...parent::rules(),
            "gender" => [new EnumRule(GenderEnum::class)]
        ];
    }
}
