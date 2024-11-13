<?php

namespace App\Features;

use App\Character;
use App\Contracts\RequestHandler;
use App\Dto\QueryRandomCharacterListCommand;
use App\Http\Resources\V4\CharacterCollection;
use Spatie\LaravelData\Optional;

/**
 * @implements RequestHandler<QueryRandomCharacterListCommand, CharacterCollection>
 */
final class QueryRandomCharacterListHandler implements RequestHandler
{
    /**
     * @inheritDoc
     */
    public function handle($request): CharacterCollection
    {
        $queryable = Character::query();
        $limit = $request->limit instanceof Optional ? 1 : $request->limit;
        $results = $queryable->random($limit);

        return new CharacterCollection($results, false);
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryRandomCharacterListCommand::class;
    }
}
