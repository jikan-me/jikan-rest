<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self mal_id()
 * @method static self name()
 * @method static self members_count()
 * @method static self created()
 *
 * @OA\Schema(
 *   schema="club_search_query_orderby",
 *   description="Club Search Query OrderBy",
 *   type="string",
 *   enum={"mal_id","name","members_count","created"}
 * )
 */
final class ClubOrderByEnum extends Enum
{
    protected static function labels(): array
    {
        return [
            "members_count" => "members"
        ];
    }
}
