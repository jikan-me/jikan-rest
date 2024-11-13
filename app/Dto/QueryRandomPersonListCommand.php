<?php

namespace App\Dto;

use App\Contracts\DataRequest;
use App\Dto\Concerns\HasLimitParameterWithSmallerMax;
use App\Http\Resources\V4\PersonCollection;
use Spatie\LaravelData\Data;

/**
 * @implements DataRequest<PersonCollection>
 */
final class QueryRandomPersonListCommand extends Data implements DataRequest
{
    use HasLimitParameterWithSmallerMax;
}
