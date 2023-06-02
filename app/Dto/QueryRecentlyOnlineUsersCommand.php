<?php

namespace App\Dto;


use App\Contracts\DataRequest;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Data;

/**
 * @extends DataRequest<JsonResponse>
 */
final class QueryRecentlyOnlineUsersCommand extends Data implements DataRequest
{
}
