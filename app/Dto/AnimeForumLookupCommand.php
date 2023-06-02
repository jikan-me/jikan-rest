<?php

namespace App\Dto;

use App\Casts\EnumCast;
use App\Enums\AnimeForumFilterEnum;
use App\Rules\Attributes\EnumValidation;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Optional;

/**
 * @extends LookupDataCommand<JsonResponse>
 */
final class AnimeForumLookupCommand extends LookupDataCommand
{
    #[WithCast(EnumCast::class, AnimeForumFilterEnum::class), EnumValidation(AnimeForumFilterEnum::class)]
    public AnimeForumFilterEnum|Optional $filter;
}
