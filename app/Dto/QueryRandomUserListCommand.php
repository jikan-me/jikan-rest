<?php

namespace App\Dto;

use App\Contracts\DataRequest;
use App\Dto\Concerns\HasLimitParameterWithSmallerMax;
use App\Http\Resources\V4\UserCollection;
use Spatie\LaravelData\Data;

/**
 * @implements DataRequest<UserCollection>
 */
final class QueryRandomUserListCommand extends Data implements DataRequest
{
    use HasLimitParameterWithSmallerMax;
}
