<?php

namespace App\Http\Controllers\V3;

use Jikan\Request\Manga\MangaCharactersRequest;
use Jikan\Request\Manga\MangaForumRequest;
use Jikan\Request\Manga\MangaMoreInfoRequest;
use Jikan\Request\Manga\MangaNewsRequest;
use Jikan\Request\Manga\MangaPicturesRequest;
use Jikan\Request\Manga\MangaRequest;
use Jikan\Request\Manga\MangaStatsRequest;

class MangaController extends Controller
{
    public function main(int $id)
    {
        $manga = $this->jikan->getManga(new MangaRequest($id));
        return response($this->serializer->serialize($manga, 'json'));
    }

    public function characters(int $id)
    {
        $manga = $this->jikan->getMangaCharacters(new MangaCharactersRequest($id));
        return response($this->serializer->serialize($manga, 'json'));
    }

    public function episodes(int $id, int $page)
    {
        $manga = $this->jikan->getMangaEpisodes(new MangaEpisodesRequest($id, $page));
        return response($this->serializer->serialize($manga, 'json'));
    }

    public function news(int $id)
    {
        $manga = $this->jikan->getNewsList(new MangaNewsRequest($id));
        return response($this->serializer->serialize($manga, 'json'));
    }

    public function forum(int $id)
    {
        $manga = $this->jikan->getMangaForum(new MangaForumRequest($id));
        return response($this->serializer->serialize($manga, 'json'));
    }

    public function pictures(int $id)
    {
        $manga = $this->jikan->getMangaPictures(new MangaPicturesRequest($id));
        return response($this->serializer->serialize($manga, 'json'));
    }

    public function stats(int $id)
    {
        $manga = $this->jikan->getMangaStats(new MangaStatsRequest($id));
        return response($this->serializer->serialize($manga, 'json'));
    }

    public function moreInfo(int $id)
    {
        $manga = $this->jikan->getMangaMoreInfo(new MangaMoreInfoRequest($id));
        return response($this->serializer->serialize($manga, 'json'));
    }
}
