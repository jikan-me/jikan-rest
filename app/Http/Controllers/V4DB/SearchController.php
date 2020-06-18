<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Http\Middleware\Throttle;
use App\Http\QueryBuilder\SearchQueryBuilderAnime;
use App\Http\Resources\V4\AnimeCollection;
use App\Http\SearchQueryBuilder;
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

    public function manga(int $page = 1)
    {
        $search = $this->jikan->getMangaSearch(
            SearchQueryBuilder::create(
                (new MangaSearchRequest())->setPage($page)
            )
        );
        return response($this->filter($search));
    }

    public function people(int $page = 1)
    {
        $search = $this->jikan->getPersonSearch(
            SearchQueryBuilder::create(
                (new PersonSearchRequest())->setPage($page)
            )
        );

        return response($this->filter($search));
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

    public function users()
    {
        $search = $this->jikan->getUserSearch(
            SearchQueryBuilder::create(
                new UserSearchRequest()
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
}
