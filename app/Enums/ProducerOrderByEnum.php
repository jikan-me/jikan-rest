<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'producers_query_orderby', description: 'Producers Search Query Order By', type: 'string', enum: ['mal_id', 'count', 'favorites', 'established'])]
final class ProducerOrderByEnum extends Enum {}
