<?php

namespace App\Enums;
use Spatie\Enum\Laravel\Enum;

/**
 * @method static self airing()
 * @method static self complete()
 * @method static self upcoming()
 *
 * @OA\Schema(
 *   schema="anime_search_query_status",
 *   description="Available Anime statuses",
 *   type="string",
 *   enum={"airing","complete","upcoming"}
 * )
 */
final class AnimeStatusEnum extends Enum
{
    protected static function labels(): array
    {
        return [
            "airing" => "Currently Airing",
            "complete" => "Finished Airing",
            "upcoming" => "Not yet aired",
        ];
    }
}
