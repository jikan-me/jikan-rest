<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self public()
 * @method static self private()
 * @method static self secret()
 *
 * @OA\Schema(
 *   schema="club_search_query_type",
 *   description="Club Search Query Type",
 *   type="string",
 *   enum={"public","private","secret"}
 * )
 */
final class ClubTypeEnum extends Enum
{
}
