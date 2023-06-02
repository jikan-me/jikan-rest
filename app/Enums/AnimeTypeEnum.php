<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self tv()
 * @method static self movie()
 * @method static self ova()
 * @method static self special()
 * @method static self ona()
 * @method static self music()
 *
 * @OA\Schema(
 *   schema="anime_search_query_type",
 *   description="Available Anime types",
 *   type="string",
 *   enum={"tv","movie","ova","special","ona","music"}
 * )
 */
final class AnimeTypeEnum extends Enum
{
    protected static function labels(): array
    {
        return [
            'tv' => 'TV',
            'movie' => 'Movie',
            'ova' => 'OVA',
            'special' => 'Special',
            'ona' => 'ONA',
            'music' => 'Music'
        ];
    }
}
