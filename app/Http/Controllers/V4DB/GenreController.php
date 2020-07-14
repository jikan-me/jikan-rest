<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\GenreAnime;
use App\GenreManga;
use App\Http\QueryBuilder\SearchQueryBuilderGenre;
use App\Http\QueryBuilder\SearchQueryBuilderProducer;
use App\Http\Resources\V4\AnimeCollection;
use App\Http\Resources\V4\GenreCollection;
use App\Http\Resources\V4\ProducerCollection;
use App\Producer;
use Illuminate\Http\Request;
use Jikan\Request\Genre\AnimeGenreRequest;
use Jikan\Request\Genre\AnimeGenresRequest;
use Jikan\Request\Genre\MangaGenreRequest;

class GenreController extends Controller
{

    private $request;
    const MAX_RESULTS_PER_PAGE = 25;

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

    public function manga(int $id, int $page = 1)
    {
        $person = $this->jikan->getMangaGenre(new MangaGenreRequest($id, $page));
        return response($this->serializer->serialize($person, 'json'));
    }

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
