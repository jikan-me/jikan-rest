<?php

namespace App\Dto;

use App\Casts\EnumCast;
use App\Contracts\DataRequest;
use App\Enums\ClubCategoryEnum;
use App\Enums\ClubOrderByEnum;
use App\Enums\ClubTypeEnum;
use App\Http\Resources\V4\ClubCollection;
use Illuminate\Support\Optional;
use Spatie\Enum\Laravel\Rules\EnumRule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\WithCast;

/**
 * @implements DataRequest<ClubCollection>
 */
final class ClubSearchCommand extends SearchCommand implements DataRequest
{
    #[WithCast(EnumCast::class, ClubCategoryEnum::class)]
    public ClubCategoryEnum|Optional $category;

    #[WithCast(EnumCast::class, ClubTypeEnum::class)]
    public ClubTypeEnum|Optional $type;

    #[MapInputName("order_by"), MapOutputName("order_by"), WithCast(EnumCast::class, ClubOrderByEnum::class)]
    public ClubOrderByEnum|Optional $orderBy;

    public static function rules(): array
    {
        return [
            ...parent::rules(),
            "category" => [new EnumRule(ClubCategoryEnum::class)],
            "type" => [new EnumRule(ClubTypeEnum::class)],
            "order_by" => [new EnumRule(ClubOrderByEnum::class)]
        ];
    }
}
