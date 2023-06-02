<?php

namespace App\Features;

use App\Contracts\CharacterRepository;
use App\Dto\QueryRandomCharacterCommand;
use App\Http\Resources\V4\CharacterResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends QueryRandomItemHandler<QueryRandomCharacterCommand, CharacterResource>
 */
final class QueryRandomCharacterHandler extends QueryRandomItemHandler
{
    public function __construct(CharacterRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryRandomCharacterCommand::class;
    }

    protected function resource(Collection $results): JsonResource
    {
        return new CharacterResource($results->first());
    }
}
