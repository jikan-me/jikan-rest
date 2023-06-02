<?php

namespace App\Features;

use App\Contracts\PeopleRepository;
use App\Contracts\RequestHandler;
use App\Dto\QueryTopPeopleCommand;
use App\Http\Resources\V4\PersonCollection;
use App\Services\QueryBuilderPaginatorService;

/**
 * @implements RequestHandler<QueryTopPeopleCommand, PersonCollection>
 */
class QueryTopPeopleHandler implements RequestHandler
{
    public function __construct(
        private readonly PeopleRepository $repository,
        private readonly QueryBuilderPaginatorService $paginatorService
    )
    {
    }

    /**
     * @param QueryTopPeopleCommand $request
     * @returns PersonCollection
     */
    public function handle($request): PersonCollection
    {
        $requestParams = collect($request->all());
        $topItemsQuery = $this->repository->topPeople()->filter($requestParams);

        $results = $this->paginatorService->paginate(
            $topItemsQuery, $requestParams->get("limit"), $requestParams->get("page")
        );

        return new PersonCollection($results);
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryTopPeopleCommand::class;
    }
}
