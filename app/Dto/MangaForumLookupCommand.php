<?php

namespace App\Dto;


use App\Casts\EnumCast;
use App\Enums\MangaForumFilterEnum;
use App\Rules\Attributes\EnumValidation;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Optional;

/**
 * @extends LookupDataCommand<JsonResponse>
 */
final class MangaForumLookupCommand extends LookupDataCommand
{
    #[WithCast(EnumCast::class, MangaForumFilterEnum::class), EnumValidation(MangaForumFilterEnum::class)]
    public MangaForumFilterEnum|Optional $filter;
}
