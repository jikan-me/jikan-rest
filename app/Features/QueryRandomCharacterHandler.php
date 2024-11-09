<?php

namespace App\Features;

use App\Character;
use App\Contracts\RequestHandler;
use App\Dto\QueryRandomCharacterCommand;
use App\Http\Resources\V4\CharacterCollection;
use App\Http\Resources\V4\CharacterResource;
use Spatie\LaravelData\Optional;

/**
 * @extends QueryRandomCharacterHandler<QueryRandomCharacterCommand, CharacterResource|CharacterCollection>
 */
final class QueryRandomCharacterHandler implements RequestHandler
{

    /**
     * @inheritDoc
     */
    public function handle($request): CharacterResource|CharacterCollection
    {
        $queryable = Character::query();

        $limit = $request->limit instanceof Optional ? 1 : $request->limit;

        $results = $queryable->random($limit);

        return $results->count() === 1
            ? new CharacterResource($results->first())
            : new CharacterCollection($results, false);
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryRandomCharacterCommand::class;
    }
}
