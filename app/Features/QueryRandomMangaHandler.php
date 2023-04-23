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

        return new MangaResource(
            $results->random()->first()
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
