<?php

namespace App\Features;

use App\Contracts\MangaRepository;
use App\Contracts\RequestHandler;
use App\Dto\QueryTopMangaItemsCommand;
use App\Enums\TopMangaFilterEnum;
use App\Http\Resources\V4\MangaCollection;
use App\Services\QueryBuilderPaginatorService;

/**
 * @implements RequestHandler<QueryTopMangaItemsCommand, MangaCollection>
 */
class QueryTopMangaItemsHandler implements RequestHandler
{
    public function __construct(private readonly MangaRepository $repository,
                                private readonly QueryBuilderPaginatorService $paginatorService)
    {
    }

    /**
     * @param QueryTopMangaItemsCommand $request
     * @returns MangaCollection
     */
    public function handle($request): MangaCollection
    {
        $requestParams = collect($request->all());
        /**
         * @var ?TopMangaFilterEnum $filterType
         */
        $filterType = $requestParams->has("filter") ? $request->filter : null;
        $builder = match($filterType) {
            TopMangaFilterEnum::publishing() => $this->repository->getTopPublishingItems(),
            TopMangaFilterEnum::upcoming() => $this->repository->getTopUpcomingItems(),
            TopMangaFilterEnum::bypopularity() => $this->repository->orderByPopularity(),
            TopMangaFilterEnum::favorite() => $this->repository->orderByFavoriteCount(),
            default => $this->repository->orderByRank()
        };

        $builder = $builder->filter($requestParams);

        return new MangaCollection(
            $this->paginatorService->paginate(
                $builder, $requestParams->get("limit"), $requestParams->get("page")
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryTopMangaItemsCommand::class;
    }
}
