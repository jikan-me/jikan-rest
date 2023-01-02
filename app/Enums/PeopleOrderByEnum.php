<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self mal_id()
 * @method static self name()
 * @method static self birthday()
 * @method static self favorites()
 *
 * @OA\Schema(
 *   schema="people_search_query_orderby",
 *   description="Available People order_by properties",
 *   type="string",
 *   enum={"mal_id", "name", "birthday", "favorites"}
 * )
 */
final class PeopleOrderByEnum extends Enum
{
    protected static function labels()
    {
        return [
            "favorites" => "member_favorites"
        ];
    }
}
