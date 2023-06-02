<?php

namespace App\Dto;

use App\Casts\EnumCast;
use App\Dto\Concerns\HasKidsParameter;
use App\Dto\Concerns\HasSfwParameter;
use App\Dto\Concerns\HasUnapprovedParameter;
use App\Dto\Concerns\MapsRouteParameters;
use App\Enums\AnimeSeasonEnum;
use App\Rules\Attributes\EnumValidation;
use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\WithCast;


final class QuerySpecificAnimeSeasonCommand extends QueryAnimeSeasonCommand
{
    use MapsRouteParameters, HasSfwParameter, HasKidsParameter, HasUnapprovedParameter;

    #[Required, Between(1000, 2999)]
    public int $year;

    #[WithCast(EnumCast::class, AnimeSeasonEnum::class), EnumValidation(AnimeSeasonEnum::class)]
    public AnimeSeasonEnum $season;

    protected static int $defaultLimit = 30;

    public static function messages(...$args): array
    {
        return [
            "season.enum" => "Invalid season supplied."
        ];
    }
}
