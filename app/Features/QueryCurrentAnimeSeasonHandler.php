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
    public function __construct(private readonly AnimeRepository $repository)
    {
    }

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
    protected function getSeasonItems($request, ?AnimeTypeEnum $type): Builder
    {
        [$season, $year] = $this->getCurrentSeason();
        /**
         * @var Carbon $from
         * @var Carbon $to
         */
        [$to, $from] = $this->getSeasonRange($year, $season);
        return $this->repository->getAiredBetween($from, $to, $type);
    }
}
