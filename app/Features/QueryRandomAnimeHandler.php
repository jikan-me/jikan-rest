<?php

namespace App\Features;

use App\Contracts\AnimeRepository;
use App\Contracts\RequestHandler;
use App\Dto\QueryRandomAnimeCommand;
use App\Http\Resources\V4\AnimeResource;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Optional;

/**
 * @implements RequestHandler<QueryRandomAnimeCommand, AnimeResource>
 */
final class QueryRandomAnimeHandler implements RequestHandler
{
    public function __construct(
        private readonly AnimeRepository $repository
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function handle($request): AnimeResource
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

        return new AnimeResource(
            $results->first()
        );
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryRandomAnimeCommand::class;
    }
}
