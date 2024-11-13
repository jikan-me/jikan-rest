<?php

namespace App\Dto;

use App\Contracts\DataRequest;
use App\Http\Resources\V4\PersonResource;
use Spatie\LaravelData\Data;

/**
 * @implements DataRequest<PersonResource>
 */
final class QueryRandomPersonCommand extends Data implements DataRequest
{
}
