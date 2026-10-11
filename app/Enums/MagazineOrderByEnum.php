<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'magazines_query_orderby', description: 'Order by magazine data', type: 'string', enum: ['mal_id', 'name', 'count'])]
final class MagazineOrderByEnum extends Enum {}
