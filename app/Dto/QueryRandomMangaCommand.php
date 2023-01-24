<?php

namespace App\Dto;

use App\Contracts\DataRequest;
use App\Http\Resources\V4\MangaResource;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * @implements DataRequest<MangaResource>
 */
final class QueryRandomMangaCommand extends Data implements DataRequest
{
    #[BooleanType]
    public bool|Optional $sfw;
}
