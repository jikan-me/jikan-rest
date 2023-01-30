<?php

namespace App\Dto;


use App\Casts\ContextualBooleanCast;
use App\Casts\EnumCast;
use App\Concerns\HasRequestFingerprint;
use App\Contracts\DataRequest;
use App\Dto\Concerns\HasLimitParameter;
use App\Dto\Concerns\HasPageParameter;
use App\Dto\Concerns\HasSfwParameter;
use App\Dto\Concerns\PreparesData;
use App\Enums\AnimeScheduleFilterEnum;
use App\Rules\Attributes\EnumValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * @implements DataRequest<JsonResponse>
 */
final class QueryAnimeSchedulesCommand extends Data implements DataRequest
{
    use HasLimitParameter, HasRequestFingerprint, HasPageParameter, PreparesData, HasSfwParameter;

    #[BooleanType, WithCast(ContextualBooleanCast::class)]
    public bool|Optional $kids = false;

    #[WithCast(EnumCast::class, AnimeScheduleFilterEnum::class), EnumValidation(AnimeScheduleFilterEnum::class)]
    public ?AnimeScheduleFilterEnum $filter;

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
