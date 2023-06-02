<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self mal_id()
 * @method static self count()
 * @method static self favorites()
 * @method static self established()
 *
 * @OA\Schema(
 *   schema="producers_query_orderby",
 *   description="Producers Search Query Order By",
 *   type="string",
 *   enum={"mal_id", "count", "favorites", "established"}
 * )
 */
final class ProducerOrderByEnum extends Enum
{
}
