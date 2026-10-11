<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;
use OpenApi\Attributes as OA;

/**
 * @method static self genres()
 * @method static self explicit_genres()
 * @method static self themes()
 * @method static self demographics()
 */

#[OA\Schema(schema: 'genre_query_filter', description: 'Filter genres by type', type: 'string', enum: ['genres', 'explicit_genres', 'themes', 'demographics'])]
final class GenreFilterEnum extends Enum {}
