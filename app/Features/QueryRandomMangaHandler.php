<?php

namespace App\Features;

use App\Contracts\MangaRepository;
use App\Contracts\RequestHandler;
use App\Dto\QueryRandomMangaCommand;
use App\Http\Resources\V4\MangaResource;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Optional;

/**
 * @implements RequestHandler<QueryRandomMangaCommand, MangaResource>
 */
final class QueryRandomMangaHandler implements RequestHandler
{
    public function __construct(
        private readonly MangaRepository $repository
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function handle($request)
    {
        $sfw = Optional::create() !== $request->sfw ? $request->sfw : null;

        /**
         * @var Collection $results;
         */
        if ($sfw) {
            $results = $this->repository->exceptItemsWithAdultRating()->random();
        } else {
            $results = $this->repository->random();
        }

        return new MangaResource(
            $results->first()
        );
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryRandomMangaCommand::class;
    }
}
