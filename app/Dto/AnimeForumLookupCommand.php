<?php

namespace App\Dto;

use App\Casts\EnumCast;
use App\Enums\AnimeForumFilterEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Optional;
use Spatie\Enum\Laravel\Rules\EnumRule;
use Spatie\LaravelData\Attributes\WithCast;

/**
 * @extends LookupDataCommand<JsonResponse>
 */
final class AnimeForumLookupCommand extends LookupDataCommand
{
    #[WithCast(EnumCast::class, AnimeForumFilterEnum::class)]
    public AnimeForumFilterEnum|Optional $filter;

    public static function rules(): array
    {
        return [
            "filter" => [new EnumRule(AnimeForumFilterEnum::class)]
        ];
    }
}
