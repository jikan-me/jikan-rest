<?php

namespace App\Dto;

use App\Casts\EnumCast;
use App\Dto\Concerns\MapsRouteParameters;
use App\Enums\AnimeSeasonEnum;
use Spatie\Enum\Laravel\Rules\EnumRule;
use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\WithCast;


final class QuerySpecificAnimeSeasonCommand extends QueryAnimeSeasonCommand
{
    use MapsRouteParameters;

    #[Required, Between(1000, 2999)]
    public int $year;

    #[WithCast(EnumCast::class, AnimeSeasonEnum::class)]
    public AnimeSeasonEnum $season;

    private static int $defaultLimit = 30;

    public static function rules(...$args): array
    {
        return [
            ...parent::rules(...$args),
            "season" => [new EnumRule(AnimeSeasonEnum::class), new Required()]
        ];
    }

    public static function messages(...$args): array
    {
        return [
            "season.enum" => "Invalid season supplied."
        ];
    }
}
