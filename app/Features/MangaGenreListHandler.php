<?php

namespace App\Features;

use App\Dto\MangaGenreListCommand;

/**
 * @extends GenreListHandler<MangaGenreListCommand>
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
