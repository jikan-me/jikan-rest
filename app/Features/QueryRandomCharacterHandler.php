<?php

namespace App\Features;

use App\Character;
use App\Contracts\RequestHandler;
use App\Dto\QueryRandomCharacterCommand;
use App\Http\Resources\V4\CharacterResource;

/**
 * @extends QueryRandomCharacterHandler<QueryRandomCharacterCommand, CharacterResource>
 */
final class QueryRandomCharacterHandler implements RequestHandler
{

    /**
     * @inheritDoc
     */
    public function handle($request): CharacterResource
    {
        return new CharacterResource(
            Character::query()
                ->random(1)
                ->first()
        );
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryRandomCharacterCommand::class;
    }
}
