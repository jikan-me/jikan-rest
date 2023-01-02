<?php

namespace App\Dto;

use App\Casts\EnumCast;
use App\Contracts\DataRequest;
use App\Enums\MangaOrderByEnum;
use App\Enums\MangaStatusEnum;
use App\Http\Resources\V4\MangaCollection;
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
    #[WithCast(EnumCast::class, MangaStatusEnum::class)]
    public MangaStatusEnum|Optional $status;

    #[StringType]
    public string|Optional $magazines;

    #[MapInputName("order_by"), MapOutputName("order_by"), WithCast(EnumCast::class, MangaOrderByEnum::class)]
    public MangaOrderByEnum|Optional $orderBy;

    public static function rules(): array
    {
        return [
            ...parent::rules(),
            "status" => new EnumRule(MangaStatusEnum::class),
            "order_by" => new EnumRule(MangaOrderByEnum::class)
        ];
    }
}
