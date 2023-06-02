<?php

namespace App\Features;

use App\Dto\QuerySpecificAnimeSeasonCommand;
use App\Enums\AnimeStatusEnum;
use App\Enums\AnimeTypeEnum;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * @extends QueryAnimeSeasonHandlerBase<QuerySpecificAnimeSeasonCommand>
 */
final class QuerySpecificAnimeSeasonHandler extends QueryAnimeSeasonHandlerBase
{
    public function requestClass(): string
    {
        return QuerySpecificAnimeSeasonCommand::class;
    }

    protected function getSeasonItems($request, ?AnimeTypeEnum $type): Builder
    {
        /**
         * @var Carbon $from
         * @var Carbon $to
         */

        [$from, $to] = $this->getSeasonRange($request->year, $request->season);
        return $this->repository->getAiredBetween($from, $to, $type)
            ->where("status", "!=", AnimeStatusEnum::upcoming()->label);
    }
}
