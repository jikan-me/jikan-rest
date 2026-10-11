<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\AnimeGenreListCommand;
use App\Dto\MangaGenreListCommand;
use App\Http\Resources\V4\GenreCollection;
use OpenApi\Attributes as OA;

class GenreController extends Controller
{
    #[OA\Get(
        path: '/genres/anime',
        operationId: 'getAnimeGenres',
        summary: 'Anime genres',
        description: 'Retrieves the anime genres for the anime identified by **id**.',
        tags: ['genres'],
        parameters: [
            new OA\Parameter(
                name: 'filter',
                in: 'query',
                schema: new OA\Schema(ref: '#/components/schemas/genre_query_filter'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns entry genres, explicit_genres, themes and demographics',
                content: new OA\JsonContent(ref: '#/components/schemas/genres'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function anime(AnimeGenreListCommand $command): GenreCollection
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/genres/manga',
        operationId: 'getMangaGenres',
        summary: 'Manga genres',
        description: 'Retrieves the manga genres for the manga identified by **id**.',
        tags: ['genres'],
        parameters: [
            new OA\Parameter(
                name: 'filter',
                in: 'query',
                schema: new OA\Schema(ref: '#/components/schemas/genre_query_filter'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns entry genres, explicit_genres, themes and demographics',
                content: new OA\JsonContent(ref: '#/components/schemas/genres'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function manga(MangaGenreListCommand $command): GenreCollection
    {
        return $this->mediator->send($command);
    }
}
