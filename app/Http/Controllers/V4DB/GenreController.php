<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\GenreAnime;
use App\GenreManga;
use App\Http\QueryBuilder\SearchQueryBuilderGenre;
use App\Http\Resources\V4\AnimeCollection;
use App\Http\Resources\V4\GenreCollection;
use App\Http\Resources\V4\MangaCollection;
use App\Manga;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    /**
     *  @OA\Get(
     *     path="/genres/anime",
     *     operationId="getAnimeGenres",
     *     tags={"genres"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns Anime Genres Resource",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function anime(Request $request)
    {
        $results = SearchQueryBuilderGenre::query(
            $request,
            GenreAnime::query()
        );

        return new GenreCollection(
            $results->get()
        );
    }

    /**
     *  @OA\Get(
     *     path="/genres/manga",
     *     operationId="getMangaGenres",
     *     tags={"genres"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns Manga Genres Resource",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function manga(Request $request)
    {
        $results = GenreManga::query();

        return new GenreCollection(
            $results->get()
        );
    }
}
