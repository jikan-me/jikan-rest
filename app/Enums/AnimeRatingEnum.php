<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self g()
 * @method static self pg()
 * @method static self pg13()
 * @method static self r17()
 * @method static self r()
 * @method static self rx()
 *
 * @OA\Schema(
 *   schema="anime_search_query_rating",
 *   description="Available Anime audience ratings<br><br><b>Ratings</b><br><ul><li>G - All Ages</li><li>PG - Children</li><li>PG-13 - Teens 13 or older</li><li>R - 17+ (violence & profanity)</li><li>R+ - Mild Nudity</li><li>Rx - Hentai</li></ul>",
 *   type="string",
 *   enum={"g","pg","pg13","r17","r","rx"}
 * )
 */
final class AnimeRatingEnum extends Enum
{
    protected static function labels(): array
    {
        return [
            "g" => "G - All Ages",
            "pg" => "PG - Children",
            "pg13" => "PG-13 - Teens 13 or older",
            "r17" => "R - 17+ (violence & profanity)",
            "r" => "R+ - Mild Nudity",
            "rx" => "Rx - Hentai"
        ];
    }
}
