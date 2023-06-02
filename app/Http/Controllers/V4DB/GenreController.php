<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\AnimeGenreListCommand;
use App\Dto\MangaGenreListCommand;
use App\Http\Resources\V4\GenreCollection;

class GenreController extends Controller
{
    /**
     *  @OA\Get(
     *     path="/genres/anime",
     *     operationId="getAnimeGenres",
     *     tags={"genres"},
     *
     *     @OA\Parameter(
     *       name="filter",
     *       in="query",
     *       @OA\Schema(ref="#/components/schemas/genre_query_filter")
     *     ),

     *     @OA\Response(
     *         response="200",
     *         description="Returns entry genres, explicit_genres, themes and demographics",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/genres"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function anime(AnimeGenreListCommand $command): GenreCollection
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/genres/manga",
     *     operationId="getMangaGenres",
     *     tags={"genres"},
     *
     *     @OA\Parameter(
     *       name="filter",
     *       in="query",
     *       @OA\Schema(ref="#/components/schemas/genre_query_filter")
     *     ),

     *     @OA\Response(
     *         response="200",
     *         description="Returns entry genres, explicit_genres, themes and demographics",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/genres"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function manga(MangaGenreListCommand $command): GenreCollection
    {
        return $this->mediator->send($command);
    }
}
