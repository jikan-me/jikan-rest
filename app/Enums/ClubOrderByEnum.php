<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'club_search_query_orderby', description: 'Club Search Query OrderBy', type: 'string', enum: ['mal_id', 'name', 'members_count', 'created'])]
final class ClubOrderByEnum extends Enum
{
    protected static function labels(): array
    {
        return [
            'members_count' => 'members',
        ];
    }
}
