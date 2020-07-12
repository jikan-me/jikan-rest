<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Club;
use App\Http\Middleware\Throttle;
use App\Http\QueryBuilder\SearchQueryBuilderAnime;
use App\Http\QueryBuilder\SearchQueryBuilderClub;
use App\Http\QueryBuilder\SearchQueryBuilderManga;
use App\Http\QueryBuilder\SearchQueryBuilderPeople;
use App\Http\QueryBuilder\SearchQueryBuilderUsers;
use App\Http\Resources\V4\AnimeCollection;
use App\Http\Resources\V4\ClubCollection;
use App\Http\Resources\V4\MangaCollection;
use App\Http\Resources\V4\PersonCollection;
use App\Http\SearchQueryBuilder;
use App\Manga;
use App\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\Jikan;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Search\AnimeSearchRequest;
use Jikan\Request\Search\MangaSearchRequest;
use Jikan\Request\Search\CharacterSearchRequest;
use Jikan\Request\Search\PersonSearchRequest;
use Jikan\Helper\Constants as JikanConstants;
use Jikan\Request\Search\UserSearchRequest;
use Jikan\Request\User\UsernameByIdRequest;
use JMS\Serializer\Serializer;
use phpDocumentor\Reflection\Types\Object_;

class SearchController extends Controller
{
    private $request;
    const MAX_RESULTS_PER_PAGE = 50;

    public function anime(Request $request)
    {
        $this->request = $request;
        $page = $this->request->get('page') ?? 1;
        $limit = $this->request->get('limit') ?? self::MAX_RESULTS_PER_PAGE;

        if (!empty($limit)) {
            $limit = (int) $limit;

            if ($limit <= 0) {
                $limit = 1;
            }

            if ($limit > self::MAX_RESULTS_PER_PAGE) {
                $limit = self::MAX_RESULTS_PER_PAGE;
            }
        }

        $results = SearchQueryBuilderAnime::query(
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

    public function manga(Request $request)
    {
        $this->request = $request;
        $page = $this->request->get('page') ?? 1;
        $limit = $this->request->get('limit') ?? self::MAX_RESULTS_PER_PAGE;

        if (!empty($limit)) {
            $limit = (int) $limit;

            if ($limit <= 0) {
                $limit = 1;
            }

            if ($limit > self::MAX_RESULTS_PER_PAGE) {
                $limit = self::MAX_RESULTS_PER_PAGE;
            }
        }

        $results = SearchQueryBuilderManga::query(
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

    public function people(Request $request)
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

        $results = SearchQueryBuilderPeople::query(
            $request,
            Person::query()
        );

        $results = $results
            ->paginate(
                $limit,
                ['*'],
                null,
                $page
            );

        return new PersonCollection(
            $results
        );
    }

    public function character(int $page = 1)
    {
        $search = $this->jikan->getCharacterSearch(
            SearchQueryBuilder::create(
                (new CharacterSearchRequest())->setPage($page)
            )
        );

        return response($this->filter($search));
    }

    public function users(Request $request)
    {
        $search = $this->jikan->getUserSearch(
            SearchQueryBuilderUsers::query(
                $request
            )
        );

        return response($this->filter($search));
    }

    public function userById(int $id)
    {
        $search = $this->jikan->getUsernameById(
            new UsernameByIdRequest($id)
        );

        return response($this->filter($search));
    }

    public function clubs(Request $request)
    {
        $this->request = $request;
        $page = $this->request->get('page') ?? 1;
        $limit = $this->request->get('limit') ?? self::MAX_RESULTS_PER_PAGE;

        if (!empty($limit)) {
            $limit = (int) $limit;

            if ($limit <= 0) {
                $limit = 1;
            }

            if ($limit > self::MAX_RESULTS_PER_PAGE) {
                $limit = self::MAX_RESULTS_PER_PAGE;
            }
        }

        $results = SearchQueryBuilderClub::query(
            $request,
            Club::query()
        );

        $results = $results
            ->paginate(
                $limit,
                ['*'],
                null,
                $page
            );

        return new ClubCollection(
            $results
        );
    }
}
