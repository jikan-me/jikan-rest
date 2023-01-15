<?php

namespace App\Dto;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Optional;
use Spatie\LaravelData\Attributes\Validation\Numeric;

/**
 * @extends LookupDataCommand<JsonResponse>
 */
final class AnimeVideosEpisodesLookupCommand extends LookupDataCommand
{
    #[Numeric]
    public int|Optional $page;
}
