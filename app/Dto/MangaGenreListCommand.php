<?php

namespace App\Dto;

use App\Contracts\DataRequest;
use App\Http\Resources\V4\GenreCollection;

/**
 * @implements DataRequest<GenreCollection>
 */
final class MangaGenreListCommand extends GenreListCommand implements DataRequest
{
}
