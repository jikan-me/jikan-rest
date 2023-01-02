<?php

namespace App\Features;

use App\Dto\AnimeGenreListCommand;

/**
 * @implements GenreListHandler<AnimeGenreListCommand>
 */
final class AnimeGenreListHandler extends GenreListHandler
{
    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return AnimeGenreListCommand::class;
    }
}
