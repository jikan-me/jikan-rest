<?php

namespace App\Dto;

use App\Casts\EnumCast;
use App\Contracts\DataRequest;
use App\Enums\ProducerOrderByEnum;
use App\Http\Resources\V4\ProducerCollection;
use Illuminate\Support\Optional;
use Spatie\Enum\Laravel\Rules\EnumRule;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\WithCast;

/**
 * @implements DataRequest<ProducerCollection>
 */
final class ProducersSearchCommand extends SearchCommand implements DataRequest
{
    #[MapInputName("order_by"), MapOutputName("order_by"), WithCast(EnumCast::class, ProducerOrderByEnum::class)]
    public ProducerOrderByEnum|Optional $orderBy;

    public static function rules(): array
    {
        return [
            "order_by" => [new EnumRule(ProducerOrderByEnum::class)]
        ];
    }
}
