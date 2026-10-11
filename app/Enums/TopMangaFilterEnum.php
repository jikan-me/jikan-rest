<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'top_manga_filter', description: 'Top items filter types', type: 'string', enum: ['publishing', 'upcoming', 'bypopularity', 'favorite'])]
final class TopMangaFilterEnum extends Enum {}
