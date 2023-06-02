<?php

namespace App\Features;

use App\Contracts\AnimeRepository;
use App\Contracts\RequestHandler;
use App\Dto\QueryTopAnimeItemsCommand;
use App\Enums\TopAnimeFilterEnum;
use App\Http\Resources\V4\AnimeCollection;
use App\Services\QueryBuilderPaginatorService;
use Spatie\LaravelData\Optional;

/**
 * @implements RequestHandler<QueryTopAnimeItemsCommand, AnimeCollection>
 */
final class QueryTopAnimeItemsHandler implements RequestHandler
{
    public function __construct(private readonly AnimeRepository $repository,
                                private readonly QueryBuilderPaginatorService $paginatorService)
    {
    }

    /**
     * @param QueryTopAnimeItemsCommand $request
     * @returns AnimeCollection
     */
    public function handle($request): AnimeCollection
    {
        $requestParams = collect($request->all());
        /**
         * @var ?TopAnimeFilterEnum $filterType
         */
        $filterType = $requestParams->has("filter") ? $request->filter : null;
        $builder = match($filterType) {
            TopAnimeFilterEnum::airing() => $this->repository->getTopAiringItems(),
            TopAnimeFilterEnum::upcoming() => $this->repository->getTopUpcomingItems(),
            TopAnimeFilterEnum::bypopularity() => $this->repository->orderByPopularity(),
            TopAnimeFilterEnum::favorite() => $this->repository->orderByFavoriteCount(),
            default => $this->repository->orderByRank()
        };

        $builder = $builder->filter($requestParams);

        return new AnimeCollection(
            $this->paginatorService->paginate(
                $builder, $requestParams->get("limit"), $requestParams->get("page")
            )
        );
    }

    public function requestClass(): string
    {
        return QueryTopAnimeItemsCommand::class;
    }
}
