<?php

namespace App\Dto;

use App\Contracts\DataRequest;
use App\Http\Resources\V4\CharacterCollection;

/**
 * @implements DataRequest<CharacterCollection>
 */
final class QueryTopCharactersCommand extends QueryTopItemsCommand implements DataRequest
{
}
