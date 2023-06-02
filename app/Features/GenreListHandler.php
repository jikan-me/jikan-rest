<?php

namespace App\Features;

use App\Contracts\GenreRepository;
use App\Contracts\RequestHandler;
use App\Dto\GenreListCommand;
use App\Enums\GenreFilterEnum;
use App\Http\Resources\V4\GenreCollection;

/**
 * @template TRequest of GenreListCommand
 * @implements RequestHandler<TRequest, GenreCollection>
 */
abstract class GenreListHandler implements RequestHandler
{
    public function __construct(private readonly GenreRepository $repository)
    {
    }

    /**
     * @param GenreListCommand $request
     * @returns GenreCollection
     */
    public function handle($request): GenreCollection
    {
        $requestParams = collect($request->all());
        /**
         * @var ?GenreFilterEnum $filterParam
         */
        $filterParam = $requestParams->has("filter") ? $request->filter : null;

        $results = match($filterParam) {
            GenreFilterEnum::genres() => $this->repository->genres(),
            GenreFilterEnum::explicit_genres() => $this->repository->getExplicitItems(),
            GenreFilterEnum::themes() => $this->repository->getThemes(),
            GenreFilterEnum::demographics() => $this->repository->getDemographics(),
            default => $this->repository->all()
        };

        return new GenreCollection($results);
    }
}
