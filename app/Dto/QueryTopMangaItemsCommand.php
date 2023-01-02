<?php

namespace App\Dto;

use App\Casts\EnumCast;
use App\Contracts\DataRequest;
use App\Enums\MangaTypeEnum;
use App\Enums\TopMangaFilterEnum;
use App\Http\Resources\V4\MangaCollection;
use Illuminate\Support\Optional;
use Spatie\Enum\Laravel\Rules\EnumRule;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\WithCast;

/**
 * @implements DataRequest<MangaCollection>
 */
final class QueryTopMangaItemsCommand extends QueryTopItemsCommand implements DataRequest
{
    #[WithCast(EnumCast::class, MangaTypeEnum::class)]
    public MangaTypeEnum|Optional $type;

    #[WithCast(EnumCast::class, TopMangaFilterEnum::class)]
    public TopMangaFilterEnum|Optional $filter;

    public static function rules(): array
    {
        return [
            "type" => [new EnumRule(MangaTypeEnum::class)],
            "filter" => [new EnumRule(TopMangaFilterEnum::class)]
        ];
    }
}
