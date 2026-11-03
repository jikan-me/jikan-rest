<?php

namespace App\Features;

use App\Dto\AnimeGenreListCommand;

/**
 * @extends GenreListHandler<AnimeGenreListCommand>
 */
final class AnimeGenreListHandler extends GenreListHandler
{
    /**
     * {@inheritDoc}
     */
    public function requestClass(): string
    {
        return AnimeGenreListCommand::class;
    }
}
