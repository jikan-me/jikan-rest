<?php

namespace App\Features;

use App\Dto\QuerySpecificAnimeSeasonCommand;
use App\Enums\AnimeSeasonEnum;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

/**
 * @extends QueryAnimeSeasonHandlerBase<QuerySpecificAnimeSeasonCommand>
 */
final class QuerySpecificAnimeSeasonHandler extends QueryAnimeSeasonHandlerBase
{
    public function requestClass(): string
    {
        return QuerySpecificAnimeSeasonCommand::class;
    }

    protected function getSeasonRangeFrom($request): array
    {
        return $this->getSeasonRange($request->year, $request->season);
    }
}
