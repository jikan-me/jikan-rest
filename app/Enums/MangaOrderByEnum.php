<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'manga_search_query_orderby', description: 'Available Manga order_by properties', type: 'string', enum: ['mal_id', 'title', 'start_date', 'end_date', 'chapters', 'volumes', 'score', 'scored_by', 'rank', 'popularity', 'members', 'favorites'])]
final class MangaOrderByEnum extends Enum
{
    protected static function labels(): array
    {
        return [
            'start_date' => 'published.from',
            'end_date' => 'published.to',
        ];
    }
}
