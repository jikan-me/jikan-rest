<?php

namespace App\Dto;

use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;

/**
 * @extends LookupDataCommand<JsonResponse>
 */
final class AnimeEpisodeLookupCommand extends LookupDataCommand
{
    #[Numeric, Required, Min(1)]
    public int $episodeId;
}
