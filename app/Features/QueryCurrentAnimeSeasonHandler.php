<?php

namespace App\Features;

use App\Contracts\AnimeRepository;
use App\Contracts\RequestHandler;
use App\Dto\QueryCurrentAnimeSeasonCommand;
use App\Enums\AnimeSeasonEnum;
use App\Enums\AnimeTypeEnum;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

/**
 * @implements RequestHandler<QueryCurrentAnimeSeasonCommand, JsonResponse>
 */
final class QueryCurrentAnimeSeasonHandler extends QueryAnimeSeasonHandlerBase
{
    public function requestClass(): string
    {
        return QueryCurrentAnimeSeasonCommand::class;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getCurrentSeason() : array
    {
        $date = new \DateTime(null, new \DateTimeZone('Asia/Tokyo'));

        $year = (int) $date->format('Y');
        $month = (int) $date->format('n');

        return match ($month) {
            1, 2, 3 => [AnimeSeasonEnum::winter(), $year],
            4, 5, 6 => [AnimeSeasonEnum::spring(), $year],
            7, 8, 9 => [AnimeSeasonEnum::summer(), $year],
            10, 11, 12 => [AnimeSeasonEnum::fall(), $year],
            default => throw new Exception('Could not generate seasonal string'),
        };
    }

    /**
     * @throws Exception
     */
    protected function getSeasonItems($request, ?AnimeTypeEnum $type): Builder
    {
        [$season, $year] = $this->getCurrentSeason();
        /**
         * @var Carbon $from
         * @var Carbon $to
         */
        [$from, $to] = $this->getSeasonRange($year, $season);
        return $this->repository->getAiredBetween($from, $to, $type);
    }
}
