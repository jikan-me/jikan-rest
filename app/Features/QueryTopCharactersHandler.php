<?php

namespace App\Features;

use App\Contracts\CharacterRepository;
use App\Contracts\RequestHandler;
use App\Dto\QueryTopCharactersCommand;
use App\Http\Resources\V4\CharacterCollection;
use App\Services\QueryBuilderPaginatorService;

/**
 * @implements RequestHandler<QueryTopCharactersCommand, CharacterCollection>
 */
final class QueryTopCharactersHandler implements RequestHandler
{
    public function __construct(
        private readonly CharacterRepository $repository,
        private readonly QueryBuilderPaginatorService $paginatorService
    )
    {
    }

    /**
     * @param QueryTopCharactersCommand $request
     * @return CharacterCollection
     */
    public function handle($request): CharacterCollection
    {
        $requestParams = collect($request->all());
        $topItemsQuery = $this->repository->topCharacters()->filter($requestParams);

        $results = $this->paginatorService->paginate(
            $topItemsQuery, $requestParams->get("limit"), $requestParams->get("page")
        );

        return new CharacterCollection($results);
    }

    public function requestClass(): string
    {
        return QueryTopCharactersCommand::class;
    }
}
