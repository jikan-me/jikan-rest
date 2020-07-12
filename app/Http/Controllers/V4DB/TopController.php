<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Http\HttpResponse;
use App\Http\QueryBuilder\SearchQueryBuilderManga;
use App\Http\QueryBuilder\TopQueryBuilderAnime;
use App\Http\QueryBuilder\TopQueryBuilderManga;
use App\Http\Resources\V4\AnimeCollection;
use App\Http\Resources\V4\MangaCollection;
use App\Manga;
use Illuminate\Support\Facades\DB;
use Jikan\Request\Top\TopAnimeRequest;
use Jikan\Request\Top\TopMangaRequest;
use Jikan\Request\Top\TopCharactersRequest;
use Jikan\Request\Top\TopPeopleRequest;
use Jikan\Helper\Constants as JikanConstants;
use Laravel\Lumen\Http\Request;

class TopController extends Controller
{
    const MAX_RESULTS_PER_PAGE = 50;

    public function anime(Request $request, int $page = 1)
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

        $results = TopQueryBuilderAnime::query(
            $request,
            Anime::query()
        );

        $results = $results
            ->paginate(
                $limit,
                ['*'],
                null,
                $page
            );

        return new AnimeCollection(
            $results
        );
    }

    public function manga(Request $request, string $type = null)
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

        $results = TopQueryBuilderManga::query(
            $request,
            Manga::query()
        );

        $results = $results
            ->paginate(
                $limit,
                ['*'],
                null,
                $page
            );

        return new MangaCollection(
            $results
        );
    }

    public function people(int $page = 1)
    {
        $top = ['top' => $this->jikan->getTopPeople(new TopPeopleRequest($page))];

        return response($this->serializer->serialize($top, 'json'));
    }

    public function characters(int $page = 1)
    {
        $top = ['top' => $this->jikan->getTopCharacters(new TopCharactersRequest($page))];

        return response($this->serializer->serialize($top, 'json'));
    }
}
