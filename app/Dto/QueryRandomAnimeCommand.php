<?php

namespace App\Dto;

use App\Contracts\DataRequest;
use App\Http\Resources\V4\AnimeResource;
use Illuminate\Support\Optional;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Data;

/**
 * @implements DataRequest<AnimeResource>
 */
final class QueryRandomAnimeCommand extends Data implements DataRequest
{
    #[BooleanType]
    public bool|Optional $sfw;
}
