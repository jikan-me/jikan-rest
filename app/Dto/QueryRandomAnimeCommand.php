<?php

namespace App\Dto;

use App\Contracts\DataRequest;
use App\Http\Resources\V4\AnimeResource;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * @implements DataRequest<AnimeResource>
 */
final class QueryRandomAnimeCommand extends Data implements DataRequest
{
    #[BooleanType]
    public bool|Optional $sfw;
}
