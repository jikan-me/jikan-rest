<?php

namespace App\Dto;

use App\Contracts\DataRequest;
use App\Dto\Concerns\HasLimitParameterWithSmallerMax;
use App\Dto\Concerns\HasSfwParameter;
use App\Dto\Concerns\HasUnapprovedParameter;
use App\Http\Resources\V4\MangaCollection;
use App\Http\Resources\V4\MangaResource;
use Spatie\LaravelData\Data;

/**
 * @implements DataRequest<MangaResource|MangaCollection>
 */
final class QueryRandomMangaCommand extends Data implements DataRequest
{
    use HasSfwParameter,
        HasUnapprovedParameter,
        HasLimitParameterWithSmallerMax;
}
