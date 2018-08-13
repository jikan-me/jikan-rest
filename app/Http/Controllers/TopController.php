<?php

namespace App\Http\Controllers;

use Jikan\Request\Top\TopAnimeRequest;
use Jikan\Request\Top\TopMangaRequest;
use Jikan\Request\Top\TopCharactersRequest;
use Jikan\Request\Top\TopPeopleRequest;
use Jikan\Helper\Constants as JikanConstants;

class TopController extends Controller
{

    public function anime(int $page = 1, string $type = null)
    {

        if (!is_null($type) && !\in_array(strtolower($type), [
                JikanConstants::TOP_AIRING,
                JikanConstants::TOP_UPCOMING,
                JikanConstants::TOP_TV,
                JikanConstants::TOP_MOVIE,
                JikanConstants::TOP_OVA,
                JikanConstants::TOP_SPECIAL,
                JikanConstants::TOP_BY_POPULARITY,
                JikanConstants::TOP_BY_FAVORITES,
            ])) {
            return response()->json([
                'error' => 'Bad Request'
            ])->setStatusCode(400);
        }

        $top = $this->jikan->getTopAnime(new TopAnimeRequest($page, $type));

        return response($this->serializer->serialize($top, 'json'));
    }

    public function manga(int $page = 1, string $type = null)
    {

        if (!is_null($type) && !\in_array(strtolower($type),
                [
                JikanConstants::TOP_MANGA,
                JikanConstants::TOP_NOVEL,
                JikanConstants::TOP_ONE_SHOT,
                JikanConstants::TOP_DOUJINSHI,
                JikanConstants::TOP_MANHWA,
                JikanConstants::TOP_MANHUA,
                JikanConstants::TOP_BY_POPULARITY,
                JikanConstants::TOP_BY_FAVORITES,
                ]
            )) {
            return response()->json([
                'error' => 'Bad Request'
            ])->setStatusCode(400);
        }

        $top = $this->jikan->getTopManga(new TopMangaRequest($page, $type));

        return response($this->serializer->serialize($top, 'json'));
    }

    public function people(int $page = 1)
    {
        $top = $this->jikan->getTopPeople(new TopPeopleRequest($page));

        return response($this->serializer->serialize($top, 'json'));
    }

    public function characters(int $page = 1)
    {
        $top = $this->jikan->getTopCharacters(new TopCharactersRequest($page));

        return response($this->serializer->serialize($top, 'json'));
    }
}
