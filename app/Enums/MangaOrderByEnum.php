<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self mal_id()
 * @method static self title()
 * @method static self start_date()
 * @method static self end_date()
 * @method static self chapters()
 * @method static self volumes()
 * @method static self score()
 * @method static self scored_by()
 * @method static self rank()
 * @method static self popularity()
 * @method static self members()
 * @method static self favorites()
 *
 * @OA\Schema(
 *   schema="manga_search_query_orderby",
 *   description="Available Manga order_by properties",
 *   type="string",
 *   enum={"mal_id", "title", "start_date", "end_date", "chapters", "volumes", "score", "scored_by", "rank", "popularity", "members", "favorites"}
 * )
 */
final class MangaOrderByEnum extends Enum
{
    protected static function labels(): array
    {
        return [
            'start_date' => 'published.from',
            'end_date' => 'published.to'
        ];
    }
}
