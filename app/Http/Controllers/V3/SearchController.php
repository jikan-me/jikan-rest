<?php

namespace App\Http\Controllers\V3;

use Illuminate\Http\Request;
use Jikan\Jikan;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Search\AnimeSearchRequest;
use Jikan\Request\Search\MangaSearchRequest;
use Jikan\Request\Search\CharacterSearchRequest;
use Jikan\Request\Search\PersonSearchRequest;
use Jikan\Helper\Constants as JikanConstants;
use App\Providers\SearchQueryBuilder;
use JMS\Serializer\Serializer;
use phpDocumentor\Reflection\Types\Object_;

class SearchController extends Controller
{
    public function anime(Request $request, int $page = 1)
    {
        $search = $this->jikan->getAnimeSearch(
            SearchQueryBuilder::create(
                $request,
                (new AnimeSearchRequest())->setPage($page)
            )
        );

        return response($this->filter($search));
    }

    public function manga(Request $request, int $page = 1)
    {
        $search = $this->jikan->getMangaSearch(
            SearchQueryBuilder::create(
                $request,
                (new MangaSearchRequest())->setPage($page)
            )
        );
        return response($this->filter($search));
    }

    public function people(Request $request, int $page = 1)
    {
        $search = $this->jikan->getPersonSearch(
            SearchQueryBuilder::create(
                $request,
                (new PersonSearchRequest())->setPage($page)
            )
        );

        return response($this->filter($search));
    }

    public function character(Request $request, int $page = 1)
    {
        $search = $this->jikan->getCharacterSearch(
            SearchQueryBuilder::create(
                $request,
                (new CharacterSearchRequest())->setPage($page)
            )
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
