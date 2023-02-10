<?php

namespace App\Dto;

use App\Casts\EnumCast;
use App\Enums\AnimeListAiringStatusFilterEnum;
use App\Enums\AnimeListStatusEnum;
use App\Enums\UserAnimeListOrderByEnum;
use App\Enums\UserListTypeEnum;
use App\Rules\Attributes\EnumValidation;
use App\Services\JikanUserListRequestMapperService;
use Carbon\CarbonImmutable;
use Jikan\Request\User\UserAnimeListRequest;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\AfterOrEqual;
use Spatie\LaravelData\Attributes\Validation\BeforeOrEqual;
use Spatie\LaravelData\Attributes\Validation\DateFormat;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

final class QueryAnimeListOfUserCommand extends QueryListOfUserCommand
{
    #[WithCast(EnumCast::class, AnimeListStatusEnum::class), EnumValidation(AnimeListStatusEnum::class)]
    public AnimeListStatusEnum|Optional $status;

    #[
        WithCast(EnumCast::class, UserAnimeListOrderByEnum::class),
        EnumValidation(UserAnimeListOrderByEnum::class),
        MapInputName("order_by")
    ]
    public UserAnimeListOrderByEnum|Optional $orderBy;

    #[
        WithCast(EnumCast::class, UserAnimeListOrderByEnum::class),
        EnumValidation(UserAnimeListOrderByEnum::class),
        MapInputName("order_by2")
    ]
    public UserAnimeListOrderByEnum|Optional $orderBy2;

    #[
        WithCast(EnumCast::class, AnimeListAiringStatusFilterEnum::class),
        EnumValidation(AnimeListAiringStatusFilterEnum::class),
        MapInputName("airing_status")
    ]
    public AnimeListAiringStatusFilterEnum|Optional $airingStatus;

    #[Min(1500), Max(2999)]
    public int|Optional $year;

    #[Min(1)]
    public int|Optional $producer;

    #[
        BeforeOrEqual("aired_to"),
        DateFormat("Y-m-d"),
        Sometimes,
        Required,
        WithCast(DateTimeInterfaceCast::class),
        WithTransformer(DateTimeInterfaceTransformer::class),
        MapInputName("aired_from")
    ]
    public CarbonImmutable|Optional $airedFrom;

    #[
        AfterOrEqual("aired_from"),
        DateFormat("Y-m-d"),
        Sometimes,
        Required,
        WithCast(DateTimeInterfaceCast::class),
        WithTransformer(DateTimeInterfaceTransformer::class),
        MapInputName("aired_to")
    ]
    public CarbonImmutable|Optional $airedTo;

    public function toJikanParserRequest(): UserAnimeListRequest
    {
        $mapper = app(JikanUserListRequestMapperService::class);
        return $mapper->map($this, UserListTypeEnum::anime());
    }
}
