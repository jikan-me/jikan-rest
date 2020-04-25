<?php

namespace App\Http\Controllers\V4;

use Jikan\Jikan;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Search\AnimeSearchRequest;
use Jikan\Request\Search\MangaSearchRequest;
use Jikan\Request\Search\CharacterSearchRequest;
use Jikan\Request\Search\PersonSearchRequest;
use Jikan\Helper\Constants as JikanConstants;
use App\Providers\SearchQueryBuilder;
use Jikan\Request\Search\UserSearchRequest;
use Jikan\Request\User\UsernameByIdRequest;
use JMS\Serializer\Serializer;
use phpDocumentor\Reflection\Types\Object_;

class SearchController extends Controller
{
    public function anime(int $page = 1)
    {
        $search = $this->jikan->getAnimeSearch(
            SearchQueryBuilder::create(
                (new AnimeSearchRequest())->setPage($page)
            )
        );

        return response($this->filter($search));
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


    private function filter($object)
    {
        $limit = $_GET['limit'] ?? null;

        $data = json_decode(
            $this->serializer->serialize($object, 'json'),
            true
        );

        if (!is_null($limit)) {
            $data['results'] = array_slice($data['results'], 0, $limit);
        }

        return json_encode(
            $data
        );
    }
}
