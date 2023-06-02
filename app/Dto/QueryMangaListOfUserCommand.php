<?php

namespace App\Dto;

use App\Casts\EnumCast;
use App\Enums\MangaListStatusEnum;
use App\Enums\UserListTypeEnum;
use App\Enums\UserMangaListOrderByEnum;
use App\Enums\UserMangaListStatusFilterEnum;
use App\Rules\Attributes\EnumValidation;
use App\Services\JikanUserListRequestMapperService;
use Carbon\CarbonImmutable;
use Jikan\Request\User\UserMangaListRequest;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\AfterOrEqual;
use Spatie\LaravelData\Attributes\Validation\BeforeOrEqual;
use Spatie\LaravelData\Attributes\Validation\DateFormat;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

final class QueryMangaListOfUserCommand extends QueryListOfUserCommand
{
    #[WithCast(EnumCast::class, MangaListStatusEnum::class), EnumValidation(MangaListStatusEnum::class)]
    public MangaListStatusEnum|Optional $status;

    #[
        WithCast(EnumCast::class, UserMangaListOrderByEnum::class),
        MapInputName("order_by"),
        EnumValidation(UserMangaListOrderByEnum::class)
    ]
    public UserMangaListOrderByEnum|Optional $orderBy;

    #[
        WithCast(EnumCast::class, UserMangaListOrderByEnum::class),
        MapInputName("order_by2"),
        EnumValidation(UserMangaListOrderByEnum::class)
    ]
    public UserMangaListOrderByEnum|Optional $orderBy2;

    #[Min(1)]
    public int|Optional $magazine;

    #[
        BeforeOrEqual("published_to"),
        DateFormat("Y-m-d"),
        Sometimes,
        Required,
        WithCast(DateTimeInterfaceCast::class),
        WithTransformer(DateTimeInterfaceTransformer::class),
        MapInputName("published_from")
    ]
    public CarbonImmutable|Optional $publishedFrom;

    #[
        AfterOrEqual("published_from"),
        DateFormat("Y-m-d"),
        Sometimes,
        Required,
        WithCast(DateTimeInterfaceCast::class),
        WithTransformer(DateTimeInterfaceTransformer::class),
        MapInputName("published_to")
    ]
    public CarbonImmutable|Optional $publishedTo;

    #[
        WithCast(EnumCast::class, UserMangaListStatusFilterEnum::class),
        EnumValidation(UserMangaListStatusFilterEnum::class),
        MapInputName("publishing_status")
    ]
    public UserMangaListStatusFilterEnum|Optional $publishingStatus;


    public function toJikanParserRequest(): UserMangaListRequest
    {
        $mapper = app(JikanUserListRequestMapperService::class);
        return $mapper->map($this, UserListTypeEnum::manga());
    }
}
