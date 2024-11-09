<?php

namespace App\Dto;

use App\Contracts\DataRequest;
use App\Dto\Concerns\HasLimitParameterWithSmallerMax;
use App\Http\Resources\V4\CharacterResource;
use Spatie\LaravelData\Data;

/**
 * @implements DataRequest<CharacterResource>
 */
final class QueryRandomCharacterCommand extends Data implements DataRequest
{
    use HasLimitParameterWithSmallerMax;
}
