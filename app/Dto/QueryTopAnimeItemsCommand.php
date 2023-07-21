<?php

namespace App\Dto;

use App\Casts\EnumCast;
use App\Contracts\DataRequest;
use App\Dto\Concerns\HasSfwParameter;
use App\Dto\Concerns\PreparesData;
use App\Enums\AnimeRatingEnum;
use App\Enums\AnimeTypeEnum;
use App\Enums\TopAnimeFilterEnum;
use App\Http\Resources\V4\AnimeCollection;
use App\Rules\Attributes\EnumValidation;
use Spatie\Enum\Laravel\Rules\EnumRule;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Optional;

/**
 * @implements DataRequest<AnimeCollection>
 */
final class QueryTopAnimeItemsCommand extends QueryTopItemsCommand implements DataRequest
{
    use PreparesData, HasSfwParameter;

    #[WithCast(EnumCast::class, AnimeTypeEnum::class), EnumValidation(AnimeTypeEnum::class)]
    public AnimeTypeEnum|Optional $type;

    #[WithCast(EnumCast::class, AnimeRatingEnum::class), EnumValidation(AnimeRatingEnum::class)]
    public AnimeRatingEnum|Optional $rating;

    #[WithCast(EnumCast::class, TopAnimeFilterEnum::class), EnumValidation(TopAnimeFilterEnum::class)]
    public TopAnimeFilterEnum|Optional $filter;
}
