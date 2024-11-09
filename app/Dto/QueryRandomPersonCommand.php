<?php

namespace App\Dto;

use App\Contracts\DataRequest;
use App\Dto\Concerns\HasLimitParameterWithSmallerMax;
use App\Http\Resources\V4\PersonCollection;
use App\Http\Resources\V4\PersonResource;
use Spatie\LaravelData\Data;

/**
 * @implements DataRequest<PersonResource|PersonCollection>
 */
final class QueryRandomPersonCommand extends Data implements DataRequest
{
    use HasLimitParameterWithSmallerMax;
}
