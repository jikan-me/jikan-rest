<?php

namespace App\Features;

use App\Contracts\AnimeRepository;
use App\Dto\QuerySpecificAnimeSeasonCommand;
use App\Enums\AnimeTypeEnum;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * @extends QueryAnimeSeasonHandlerBase<QuerySpecificAnimeSeasonCommand>
 */
final class QuerySpecificAnimeSeasonHandler extends QueryAnimeSeasonHandlerBase
{
    public function __construct(private readonly AnimeRepository $repository)
    {
    }

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
        return $this->repository->getAiredBetween($from, $to, $type, $request->kids, $request->sfw, $request->unapproved);
    }
}
