<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self mal_id()
 * @method static self name()
 * @method static self count()
 *
 * @OA\Schema(
 *    schema="magazines_query_orderby",
 *    description="Order by magazine data",
 *    type="string",
 *    enum={"mal_id", "name", "count"}
 *  )
 */
final class MagazineOrderByEnum extends Enum
{
}
