<?php

namespace App\Enums;
use Spatie\Enum\Laravel\Enum;

/**
 * @method static self asc()
 * @method static self desc()
 *
 * @OA\Schema(
 *   schema="search_query_sort",
 *   description="Search query sort direction",
 *   type="string",
 *   enum={"desc","asc"}
 * )
 */
final class SortDirection extends Enum
{
}
