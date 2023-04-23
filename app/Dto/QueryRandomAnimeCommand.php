<?php

namespace App\Dto;

use App\Contracts\DataRequest;
use App\Dto\Concerns\HasSfwParameter;
use App\Dto\Concerns\HasUnapprovedParameter;
use App\Http\Resources\V4\AnimeResource;
use Spatie\LaravelData\Data;

/**
 * @implements DataRequest<AnimeResource>
 */
final class QueryRandomAnimeCommand extends Data implements DataRequest
{
    use HasSfwParameter, HasUnapprovedParameter;
}
