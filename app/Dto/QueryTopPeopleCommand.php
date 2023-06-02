<?php

namespace App\Dto;

use App\Contracts\DataRequest;
use App\Http\Resources\V4\PersonCollection;

/**
 * @implements DataRequest<PersonCollection>
 */
final class QueryTopPeopleCommand extends QueryTopItemsCommand implements DataRequest
{
}
