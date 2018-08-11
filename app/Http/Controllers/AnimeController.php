<?php

namespace App\Http\Controllers;

use Jikan\Request\Anime\AnimeCharactersAndStaffRequest;
use Jikan\Request\Anime\AnimeEpisodesRequest;
use Jikan\Request\Anime\AnimeForumRequest;
use Jikan\Request\Anime\AnimeMoreInfoRequest;
use Jikan\Request\Anime\AnimeNewsRequest;
use Jikan\Request\Anime\AnimePicturesRequest;
use Jikan\Request\Anime\AnimeRequest;
use Jikan\Request\Anime\AnimeStatsRequest;
use Jikan\Request\Anime\AnimeVideosRequest;

class AnimeController extends Controller
{
    public function main(int $id)
    {
        $anime = $this->jikan->getAnime(new AnimeRequest($id));
        return response($this->serializer->serialize($anime, 'json'));
    }

    public function characters_staff(int $id)
    {
        $anime = $this->jikan->getAnimeCharactersAndStaff(new AnimeCharactersAndStaffRequest($id));
        return response($this->serializer->serialize($anime, 'json'));
    }

    public function episodes(int $id, int $page)
    {
        $anime = $this->jikan->getAnimeEpisodes(new AnimeEpisodesRequest($id, $page));
        return response($this->serializer->serialize($anime, 'json'));
    }

    public function news(int $id)
    {
        $anime = $this->jikan->getNewsList(new AnimeNewsRequest($id));
        return response($this->serializer->serialize($anime, 'json'));
    }

    public function forum(int $id)
    {
        $anime = $this->jikan->getAnimeForum(new AnimeForumRequest($id));
        return response($this->serializer->serialize($anime, 'json'));
    }

    public function videos(int $id)
    {
        $anime = $this->jikan->getAnimeVideos(new AnimeVideosRequest($id));
        return response($this->serializer->serialize($anime, 'json'));
    }

    public function pictures(int $id)
    {
        $anime = $this->jikan->getAnimePictures(new AnimePicturesRequest($id));
        return response($this->serializer->serialize($anime, 'json'));
    }

    public function stats(int $id)
    {
        $anime = $this->jikan->getAnimeStats(new AnimeStatsRequest($id));
        return response($this->serializer->serialize($anime, 'json'));
    }

    public function moreInfo(int $id)
    {
        $anime = $this->jikan->getAnimeMoreInfo(new AnimeMoreInfoRequest($id));
        return response($this->serializer->serialize($anime, 'json'));
    }
}
