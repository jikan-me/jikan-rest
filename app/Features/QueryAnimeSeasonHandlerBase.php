<?php

namespace App\Features;

use App\Contracts\AnimeRepository;
use App\Contracts\RequestHandler;
use App\Dto\QueryAnimeSeasonCommand;
use App\Enums\AnimeSeasonEnum;
use App\Enums\AnimeTypeEnum;
use App\Http\Resources\V4\AnimeCollection;
use App\Support\CachedData;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Optional;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

/**
 * @template TRequest of QueryAnimeSeasonCommand
 * @implements RequestHandler<TRequest, JsonResponse>
 */
abstract class QueryAnimeSeasonHandlerBase implements RequestHandler
{
    public function __construct(protected readonly AnimeRepository $repository)
    {
    }

    /**
     * @param QueryAnimeSeasonCommand $request
     * @return JsonResponse
     */
    public function handle($request): JsonResponse
    {
        $requestParams = collect($request->all());
        $type = $requestParams->has("filter") ? $request->filter : null;
        $limit = $request->limit instanceof Optional ? max_results_per_page() : $request->limit;
        $results = $this->getSeasonItems($request, $type);
        // apply sfw, kids and unapproved filters
        /** @noinspection PhpUndefinedMethodInspection */
        $results = $results->filter($requestParams);
        $results = $results->paginate($limit, ["*"], null, $request->page);

        $animeCollection = new AnimeCollection($results);
        $response = $animeCollection->response();

        return $response->addJikanCacheFlags($request->getFingerPrint(), CachedData::from($animeCollection->collection));
    }

    /**
     * @param TRequest $request
     * @param ?AnimeTypeEnum $type
     * @return Builder
     */
    protected abstract function getSeasonItems($request, ?AnimeTypeEnum $type): Builder;

    protected function getSeasonRange(int $year, AnimeSeasonEnum $season): array
    {
        [$monthStart, $monthEnd] = match ($season->value) {
            AnimeSeasonEnum::winter()->value => [1, 3],
            AnimeSeasonEnum::spring()->value => [4, 6],
            AnimeSeasonEnum::summer()->value => [7, 9],
            AnimeSeasonEnum::fall()->value => [10, 12],
            default => throw new BadRequestException('Invalid season supplied'),
        };

        $from = Carbon::createFromDate($year, $monthStart, 1)
            ->setTimezone(new \DateTimeZone('UTC'))
            ->setTime(0, 0);

        $to = Carbon::createFromDate($year, $monthEnd, 1)
            ->setTimezone(new \DateTimeZone('UTC'))
            ->setTime(0,0);

        return [
            $from,
            $to
        ];
    }
}
