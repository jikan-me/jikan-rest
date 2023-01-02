<?php

namespace App\Features;

use App\Dto\CharactersSearchCommand;
use App\Http\Resources\V4\CharacterCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends SearchRequestHandler<CharactersSearchCommand, CharacterCollection>
 */
class CharacterSearchHandler extends SearchRequestHandler
{
    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return CharactersSearchCommand::class;
    }

    /**
     * @inheritDoc
     */
    protected function renderResponse(LengthAwarePaginator $paginator)
    {
        return new CharacterCollection($paginator);
    }
}
