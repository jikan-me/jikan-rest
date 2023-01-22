<?php

namespace App\Features;

use App\Contracts\RequestHandler;
use App\Dto\QueryCurrentAnimeSeasonCommand;
use App\Enums\AnimeSeasonEnum;
use Exception;
use Illuminate\Http\JsonResponse;

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
            in_array($month, range(1, 3)) => [AnimeSeasonEnum::winter(), $year],
            in_array($month, range(4, 6)) => [AnimeSeasonEnum::spring(), $year],
            in_array($month, range(7, 9)) => [AnimeSeasonEnum::summer(), $year],
            in_array($month, range(10, 12)) => [AnimeSeasonEnum::fall(), $year],
            default => throw new Exception('Could not generate seasonal string'),
        };
    }

    /**
     * @throws Exception
     */
    protected function getSeasonRangeFrom($request): array
    {
        /**
         * @var AnimeSeasonEnum $season
         * @var int $year
         */
        [$season, $year] = $this->getCurrentSeason();

        return $this->getSeasonRange($year, $season);
    }
}
