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
        $unapproved = Optional::create() !== $request->unapproved ? $request->unapproved : null;

        /**
         * @var Collection $results;
         */
        $results = $this->repository;

        if (!$unapproved) {
            $results->excludeUnapprovedItems($results);
        }

        if ($sfw) {
            $results->excludeNsfwItems($results);
        }

        return new AnimeResource(
            $results->random()->first()
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
