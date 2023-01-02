<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self mal_id()
 * @method static self title()
 * @method static self type()
 * @method static self rating()
 * @method static self start_date()
 * @method static self end_date()
 * @method static self episodes()
 * @method static self score()
 * @method static self scored_by()
 * @method static self rank
 * @method static self popularity()
 * @method static self members()
 * @method static self favorites()
 *
 * @OA\Schema(
 *   schema="anime_search_query_orderby",
 *   description="Available Anime order_by properties",
 *   type="string",
 *   enum={"mal_id", "title", "type", "rating", "start_date", "end_date", "episodes", "score", "scored_by", "rank", "popularity", "members", "favorites" }
 * )
 */
final class AnimeOrderByEnum extends Enum
{
    protected static function labels(): array
    {
        return [
            'start_date' => 'aired.from',
            'end_date' => 'aired.to',
        ];
    }
}
