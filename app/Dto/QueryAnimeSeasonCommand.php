<?php

namespace App\Dto;


use App\Casts\EnumCast;
use App\Concerns\HasRequestFingerprint;
use App\Contracts\DataRequest;
use App\Enums\AnimeSeasonEnum;
use App\Enums\AnimeTypeEnum;
use App\Http\HttpHelper;
use App\Http\Resources\V4\AnimeCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Env;
use Spatie\Enum\Laravel\Rules\EnumRule;
use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Resolvers\DataFromSomethingResolver;

/**
 * @implements DataRequest<AnimeCollection>
 */
final class QueryAnimeSeasonCommand extends Data implements DataRequest
{
    use HasRequestFingerprint;

    #[Required, Between(1000, 2999)]
    public int $year;

    #[WithCast(EnumCast::class, AnimeSeasonEnum::class)]
    public AnimeSeasonEnum $season;

    #[WithCast(EnumCast::class, AnimeTypeEnum::class)]
    public AnimeTypeEnum|Optional $filter;

    #[Numeric, Min(1)]
    public int|Optional $page = 1;

    #[Numeric, Min(1)]
    public int|Optional $limit;

    public static function rules(...$args): array
    {
        return [
            "season" => [new EnumRule(AnimeSeasonEnum::class), new Required()],
            "filter" => [new EnumRule(AnimeTypeEnum::class)]
        ];
    }

    public static function messages(...$args): array
    {
        return [
            "season.enum" => "Invalid season supplied."
        ];
    }

    /** @noinspection PhpUnused */
    public static function fromRequestAndRequired(Request $request, int $year, AnimeSeasonEnum $season): self
    {
        /**
         * @var QueryAnimeSeasonCommand $data
         */
        $data = app(DataFromSomethingResolver::class)
                    ->withoutMagicalCreation()->execute(self::class, ["request" => $request, "year" => $year, "season" => $season->value]);
        $data->fingerprint = HttpHelper::resolveRequestFingerprint($request);
        if ($data->limit == Optional::create()) {
            $data->limit = Env::get("MAX_RESULTS_PER_PAGE", 30);
        }

        return $data;
    }
}
