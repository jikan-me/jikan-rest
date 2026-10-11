<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'club_search_query_category', description: 'Club Search Query Category', type: 'string', enum: ['anime', 'manga', 'actors_and_artists', 'characters', 'cities_and_neighborhoods', 'companies', 'conventions', 'games', 'japan', 'music', 'other', 'schools'])]
final class ClubCategoryEnum extends Enum
{
    protected static function labels(): array
    {
        return [
            'anime' => 'Anime',
            'manga' => 'Manga',
            'actors_and_artists' => 'Actors & Artists',
            'characters' => 'Characters',
            'cities_and_neighborhoods' => 'Cities & Neighborhoods',
            'companies' => 'Companies',
            'conventions' => 'Conventions',
            'games' => 'Games',
            'japan' => 'Japan',
            'music' => 'Music',
            'other' => 'Other',
            'schools' => 'Schools',
        ];
    }
}
