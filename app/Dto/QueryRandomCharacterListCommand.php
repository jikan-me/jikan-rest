<?php

namespace App\Dto;

use App\Contracts\DataRequest;
use App\Dto\Concerns\HasLimitParameterWithSmallerMax;
use App\Http\Resources\V4\CharacterCollection;
use Spatie\LaravelData\Data;

/**
 * @implements DataRequest<CharacterCollection>
 */
final class QueryRandomCharacterListCommand extends Data implements DataRequest
{
    use HasLimitParameterWithSmallerMax;
}
