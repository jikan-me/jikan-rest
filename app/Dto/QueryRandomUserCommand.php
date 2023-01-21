<?php

namespace App\Dto;

use App\Contracts\DataRequest;
use App\Http\Resources\V4\ProfileResource;
use Spatie\LaravelData\Data;

/**
 * @implements DataRequest<ProfileResource>
 */
final class QueryRandomUserCommand extends Data implements DataRequest
{
}
