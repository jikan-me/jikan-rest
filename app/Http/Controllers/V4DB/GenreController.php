<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\GenreAnime;
use App\GenreManga;
use App\Http\QueryBuilder\SearchQueryBuilderGenre;
use App\Http\QueryBuilder\SearchQueryBuilderProducer;
use App\Http\Resources\V4\AnimeCollection;
use App\Http\Resources\V4\GenreCollection;
use App\Http\Resources\V4\MangaCollection;
use App\Http\Resources\V4\ProducerCollection;
use App\Manga;
use App\Producer;
use Illuminate\Http\Request;
use Jikan\Request\Genre\AnimeGenreRequest;
use Jikan\Request\Genre\AnimeGenresRequest;
use Jikan\Request\Genre\MangaGenreRequest;

class GenreController extends Controller
{

    private $request;
    const MAX_RESULTS_PER_PAGE = 25;

    /**
     *  @OA\Get(
     *     path="/genres/anime/{id}",
     *     operationId="getAnimeGenreById",
     *     tags={"anime collection"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns Genres's anime",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function anime(Request $request, int $id)
    {
        $this->request = $request;
        $page = $this->request->get('page') ?? 1;

        $results = Anime::query()
            ->where('genres.mal_id', $id)
            ->orderBy('title');

        $results = $results
            ->paginate(
                self::MAX_RESULTS_PER_PAGE,
                ['*'],
                null,
                $page
            );

        return new AnimeCollection(
            $results
        );
    }
    /**
     *  @OA\Get(
     *     path="/genres/manga/{id}",
     *     operationId="getMangaGenreById",
     *     tags={"manga collection"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns Genres's manga",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function manga(Request $request, int $id)
    {
        $page = $request->get('page') ?? 1;

        $results = Manga::query()
            ->where('genres.mal_id', $id)
            ->orderBy('title');

        $results = $results
            ->paginate(
                self::MAX_RESULTS_PER_PAGE,
                ['*'],
                null,
                $page
            );

        return new MangaCollection(
            $results
        );
    }

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
    public function mainAnime(Request $request)
    {
        $page = $request->get('page') ?? 1;
        $limit = $request->get('limit') ?? self::MAX_RESULTS_PER_PAGE;

        if (!empty($limit)) {
            $limit = (int) $limit;

            if ($limit <= 0) {
                $limit = 1;
            }

            if ($limit > self::MAX_RESULTS_PER_PAGE) {
                $limit = self::MAX_RESULTS_PER_PAGE;
            }
        }

        $results = SearchQueryBuilderGenre::query(
            $request,
            GenreAnime::query()
        );

        $results = $results
            ->paginate(
                $limit,
                ['*'],
                null,
                $page
            );

        return new GenreCollection(
            $results
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
    public function mainManga(Request $request)
    {
        $page = $request->get('page') ?? 1;
        $limit = $request->get('limit') ?? self::MAX_RESULTS_PER_PAGE;

        if (!empty($limit)) {
            $limit = (int) $limit;

            if ($limit <= 0) {
                $limit = 1;
            }

            if ($limit > self::MAX_RESULTS_PER_PAGE) {
                $limit = self::MAX_RESULTS_PER_PAGE;
            }
        }

        $results = SearchQueryBuilderGenre::query(
            $request,
            GenreManga::query()
        );

        $results = $results
            ->paginate(
                $limit,
                ['*'],
                null,
                $page
            );

        return new GenreCollection(
            $results
        );
    }
}
