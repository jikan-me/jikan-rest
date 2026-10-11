<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'anime_search_query_type', description: 'Available Anime types', type: 'string', enum: ['tv', 'movie', 'ova', 'special', 'ona', 'music', 'cm', 'pv', 'tv_special'])]
#[OA\Schema(schema: 'anime_types', type: 'string', description: 'Anime Type', enum: ['TV', 'OVA', 'Movie', 'Special', 'ONA', 'Music', 'CM', 'PV', 'TV Special'])]
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
            'music' => 'Music',
            'cm' => 'CM',
            'pv' => 'PV',
            'tv_special' => 'TV Special',
        ];
    }
}
