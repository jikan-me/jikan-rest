<?php

namespace App\Features;

use App\Dto\MangaGenreListCommand;

/**
 * @implements GenreListHandler<MangaGenreListCommand>
 */
final class MangaGenreListHandler extends GenreListHandler
{
    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return MangaGenreListCommand::class;
    }
}
