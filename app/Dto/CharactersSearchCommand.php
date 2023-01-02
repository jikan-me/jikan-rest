<?php

namespace App\Dto;

use App\Casts\EnumCast;
use App\Contracts\DataRequest;
use App\Enums\CharacterOrderByEnum;
use App\Http\Resources\V4\CharacterCollection;
use Illuminate\Support\Optional;
use Spatie\Enum\Laravel\Rules\EnumRule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\WithCast;

/**
 * @implements DataRequest<CharacterCollection>
 */
final class CharactersSearchCommand extends SearchCommand implements DataRequest
{
    #[MapInputName("order_by"), MapOutputName("order_by"), WithCast(EnumCast::class, CharacterOrderByEnum::class)]
    public CharacterOrderByEnum|Optional $orderBy;

    public static function rules(): array
    {
        return [
            "order_by" => [new EnumRule(CharacterOrderByEnum::class)]
        ];
    }
}
