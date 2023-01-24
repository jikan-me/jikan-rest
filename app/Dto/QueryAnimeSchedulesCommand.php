<?php

namespace App\Dto;


use App\Casts\EnumCast;
use App\Concerns\HasRequestFingerprint;
use App\Contracts\DataRequest;
use App\Dto\Concerns\HasLimitParameter;
use App\Dto\Concerns\HasPageParameter;
use App\Enums\AnimeScheduleFilterEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Enum\Laravel\Rules\EnumRule;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * @implements DataRequest<JsonResponse>
 */
final class QueryAnimeSchedulesCommand extends Data implements DataRequest
{
    use HasLimitParameter, HasRequestFingerprint, HasPageParameter;

    #[BooleanType]
    public bool|Optional $kids = false;

    #[BooleanType]
    public bool|Optional $sfw = false;

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
