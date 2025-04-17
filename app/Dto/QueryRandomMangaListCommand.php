<?php

namespace App\Dto;

use App\Contracts\DataRequest;
use App\Dto\Concerns\HasLimitParameterWithSmallerMax;
use App\Dto\Concerns\HasSfwParameter;
use App\Dto\Concerns\HasUnapprovedParameter;
use App\Http\Resources\V4\MangaCollection;
use Spatie\LaravelData\Data;

/**
 * @implements DataRequest<MangaCollection>
 */
final class QueryRandomMangaListCommand extends Data implements DataRequest
{
    use HasSfwParameter,
        HasUnapprovedParameter,
        HasLimitParameterWithSmallerMax;
}
