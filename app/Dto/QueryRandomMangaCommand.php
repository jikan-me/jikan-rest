<?php

namespace App\Dto;

use App\Contracts\DataRequest;
use App\Http\Resources\V4\MangaResource;
use Illuminate\Support\Optional;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Data;

/**
 * @implements DataRequest<MangaResource>
 */
final class QueryRandomMangaCommand extends Data implements DataRequest
{
    #[BooleanType]
    public bool|Optional $sfw;
}
