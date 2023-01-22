<?php

namespace App\Dto;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Optional;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;

/**
 * @extends LookupDataCommand<JsonResponse>
 */
final class AnimeVideosEpisodesLookupCommand extends LookupDataCommand
{
    #[Numeric, Min(1)]
    public int|Optional $page = 1;
}
