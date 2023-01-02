<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self anime()
 * @method static self manga()
 * @method static self actors_and_artists()
 * @method static self characters()
 * @method static self cities_and_neighborhoods()
 * @method static self companies()
 * @method static self conventions()
 * @method static self games()
 * @method static self japan()
 * @method static self music()
 * @method static self other()
 * @method static self schools()
 *
 * @OA\Schema(
 *   schema="club_search_query_category",
 *   description="Club Search Query Category",
 *   type="string",
 *   enum={
 *      "anime","manga","actors_and_artists","characters",
 *      "cities_and_neighborhoods","companies","conventions","games",
 *      "japan","music","other","schools"
 *   }
 * )
 */
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
            'schools' => 'Schools'
        ];
    }
}
