<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self mal_id()
 * @method static self name()
 * @method static self favorites()
 *
 * @OA\Schema(
 *   schema="characters_search_query_orderby",
 *   description="Available Character order_by properties",
 *   type="string",
 *   enum={"mal_id", "name", "favorites"}
 * )
 */
final class CharacterOrderByEnum extends Enum
{
    protected static function labels(): array
    {
        return [
            "favorites" => "member_favorites"
        ];
    }
}
