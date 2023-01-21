<?php

namespace App\Dto;


use App\Casts\EnumCast;
use App\Concerns\HasRequestFingerprint;
use App\Contracts\DataRequest;
use App\Enums\AnimeScheduleFilterEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Enum\Laravel\Rules\EnumRule;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

/**
 * @implements DataRequest<JsonResponse>
 */
final class QueryAnimeSchedulesCommand extends Data implements DataRequest
{
    use HasRequestFingerprint;

    #[Numeric, Min(1)]
    public int $page = 1;

    #[IntegerType, Min(1), Nullable]
    public ?int $limit;

    #[BooleanType]
    public bool $kids = false;

    #[BooleanType]
    public bool $sfw = false;

    #[WithCast(EnumCast::class, AnimeScheduleFilterEnum::class)]
    public ?AnimeScheduleFilterEnum $filter;

    public static function rules(...$args): array
    {
        return [
            "filter" => [new EnumRule(AnimeScheduleFilterEnum::class), new Nullable()]
        ];
    }

    /** @noinspection PhpUnused */
    public static function fromRequestAndDay(Request $request, ?string $day): self
    {
        /**
         * @var QueryAnimeSchedulesCommand $data
         */
        $data = self::fromRequest($request);

        if (!is_null($day)) {
            $data->filter = AnimeScheduleFilterEnum::from($day);
        }

        return $data;
    }
}
