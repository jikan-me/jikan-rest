<?php

namespace App\Dto;

use App\Contracts\DataRequest;
use App\Dto\Concerns\HasLimitParameterWithSmallerMax;
use App\Dto\Concerns\HasSfwParameter;
use App\Dto\Concerns\HasUnapprovedParameter;
use App\Http\Resources\V4\AnimeCollection;
use Spatie\LaravelData\Data;

/**
 * @implements DataRequest<AnimeCollection>
 */
final class QueryRandomAnimeListCommand extends Data implements DataRequest
{
    use HasSfwParameter,
        HasUnapprovedParameter,
        HasLimitParameterWithSmallerMax;
}
