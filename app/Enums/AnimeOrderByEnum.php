<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'anime_search_query_orderby', description: 'Available Anime order_by properties', type: 'string', enum: ['mal_id', 'title', 'start_date', 'end_date', 'episodes', 'score', 'scored_by', 'rank', 'popularity', 'members', 'favorites'])]
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
