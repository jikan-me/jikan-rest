<?php

namespace App\Dto;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;

/**
 * @extends LookupDataCommand<JsonResponse>
 */
final class AnimeEpisodeLookupCommand extends LookupDataCommand
{
    #[Numeric, Required]
    public int $episodeId;

    /** @noinspection PhpUnused */
    public static function fromMultiple(Request $request, int $id, int $episodeId): ?self
    {
        /**
         * @var AnimeEpisodeLookupCommand $data
         */
        $data = self::fromRequestAndKey($request, $id);
        $data->episodeId = $episodeId;

        return $data;
    }
}
