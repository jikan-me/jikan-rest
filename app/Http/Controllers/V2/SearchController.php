<?php

namespace App\Http\Controllers\V2;


use Jikan\Jikan;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Search\AnimeSearchRequest;
use Jikan\Request\Search\MangaSearchRequest;
use Jikan\Request\Search\CharacterSearchRequest;
use Jikan\Request\Search\PersonSearchRequest;
use Jikan\Helper\Constants as JikanConstants;
use App\Providers\SearchQueryBuilder;
use JMS\Serializer\Serializer;

class SearchController extends Controller
{

    public function anime(int $page = 1) {
        $search = $this->jikan->getAnimeSearch(
            SearchQueryBuilder::create(
                (new AnimeSearchRequest())->setPage($page)
            )
        );

        return response($this->serializer->serialize($search, 'json'));
    }

    public function manga(int $page = 1) {
        $search = $this->jikan->getMangaSearch(
            SearchQueryBuilder::create(
                (new MangaSearchRequest())->setPage($page)
            )
        );

        return response($this->serializer->serialize($search, 'json'));
    }

    public function people(int $page = 1) {
        $search = $this->jikan->getPersonSearch(
            SearchQueryBuilder::create(
                (new PersonSearchRequest())->setPage($page)
            )
        );

        // backwards compatibility
        $search = json_decode(
            $this->serializer->serialize($search, 'json'),
            true
        );

        foreach ($search['result'] as &$item) {
            $item['nicknames'] = empty($item['nicknames']) ? null : implode(",", $item['nicknames']);
        }

        return $search;
    }

    public function character(int $page = 1) {
        $search = $this->jikan->getCharacterSearch(
            SearchQueryBuilder::create(
                (new CharacterSearchRequest())->setPage($page)
            )
        );

        // backwards compatibility
        $search = json_decode(
            $this->serializer->serialize($search, 'json'),
            true
        );

        foreach ($search['result'] as &$item) {
            $item['nicknames'] = empty($item['nicknames']) ? null : implode(",", $item['nicknames']);
        }

        return $search;
    }

}