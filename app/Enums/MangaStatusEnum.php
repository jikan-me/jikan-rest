<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'manga_search_query_status', description: 'Available Manga statuses', type: 'string', enum: ['publishing', 'complete', 'hiatus', 'discontinued', 'upcoming'])]
final class MangaStatusEnum extends Enum
{
    protected static function labels(): array
    {
        return [
            'publishing' => 'Publishing',
            'complete' => 'Finished',
            'hiatus' => 'On Hiatus',
            'discontinued' => 'Discontinued',
            'upcoming' => 'Not yet published',
        ];
    }
}
