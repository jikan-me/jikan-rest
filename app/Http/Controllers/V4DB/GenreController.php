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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;

class GenreController extends Controller
{
    /**
     *  @OA\Get(
     *     path="/genres/anime",
     *     operationId="getAnimeGenres",
     *     tags={"genres"},
     *
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *     @OA\Parameter(ref="#/components/parameters/limit"),
     *
     *     @OA\Parameter(
     *       name="filter",
     *       in="query",
     *       @OA\Schema(ref="#/components/schemas/genre query filter")
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
    public function anime(Request $request): GenreCollection
    {
        $filter = $request->get('filter') ?? null;

        $explicitGenres = DB::table('explicit_genres_anime')->get();
        $themes = DB::table('themes_anime')->get();
        $demographics = DB::table('demographics_anime')->get();

        switch ($filter) {
            case 'genres':
                $results = GenreAnime::query()
                    ->get();
                break;
            case 'explicit_genres':
                $results = $explicitGenres;
                break;
            case 'themes':
                $results = $themes;
                break;
            case 'demographics':
                $results = $demographics;
                break;
            default:
                $results = GenreAnime::query()
                    ->get()
                    ->concat($explicitGenres->all())
                    ->concat($themes->all())
                    ->concat($demographics->all());
                break;
        }

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
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *     @OA\Parameter(ref="#/components/parameters/limit"),
     *
     *     @OA\Parameter(
     *       name="filter",
     *       in="query",
     *       @OA\Schema(ref="#/components/schemas/genre query filter")
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
    public function manga(Request $request): GenreCollection
    {
        $filter = $request->get('filter') ?? null;

        $explicitGenres = DB::table('explicit_genres_manga')->get();
        $themes = DB::table('themes_manga')->get();
        $demographics = DB::table('demographics_manga')->get();

        switch ($filter) {
            case 'genres':
                $results = GenreManga::query()
                    ->get();
                break;
            case 'explicit_genres':
                $results = $explicitGenres;
                break;
            case 'themes':
                $results = $themes;
                break;
            case 'demographics':
                $results = $demographics;
                break;
            default:
                $results = GenreManga::query()
                    ->get()
                    ->concat($explicitGenres->all())
                    ->concat($themes->all())
                    ->concat($demographics->all());
                break;
        }

        return new GenreCollection(
            $results
        );
    }
}
