<?php

namespace App\Features;

use App\Dto\CharactersSearchCommand;
use App\Http\Resources\V4\CharacterCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends SearchRequestHandler<CharactersSearchCommand, CharacterCollection>
 */
final class CharacterSearchHandler extends SearchRequestHandler
{
    /**
     * {@inheritDoc}
     */
    public function requestClass(): string
    {
        return CharactersSearchCommand::class;
    }

    /**
     * @param  LengthAwarePaginator<int, mixed>  $paginator
     */
    protected function renderResponse(LengthAwarePaginator $paginator): CharacterCollection
    {
        return new CharacterCollection($paginator);
    }
}
