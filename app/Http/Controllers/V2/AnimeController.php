<?php

namespace App\Http\Controllers\V2;

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
    public function _main($id) {
        $anime = $this->jikan->getAnime(new AnimeRequest($id));

        // backwards compatibility
        $anime = json_decode(
            $this->serializer->serialize($anime, 'json'),
            true
        );

        $anime['aired_string'] = $anime['aired']['string'];
        unset($anime['aired']['string']);
        $anime['title_synonyms'] = empty($anime['title_synonyms']) ? null : implode(",", $anime['title_synonyms']);;

        return $anime;
    }

    public function main(int $id)
    {
        $anime = $this->_main($id);

        return response($anime);
    }

    public function characters_staff(int $id)
    {
        $anime = $this->_main($id);
        $charactersStaff = $this->jikan->getAnimeCharactersAndStaff(new AnimeCharactersAndStaffRequest($id));
        $charactersStaff = json_decode(
            $this->serializer->serialize($charactersStaff, 'json'),
            true
        );

        foreach ($charactersStaff['staff'] as &$staff) {
            $staff['positions'] = empty($staff['positions']) ?  null : implode(",", $staff['positions']);
            $staff['role'] = $staff['positions'];
            unset($staff['positions']);
        }


        return response(
            array_merge(
                $anime,
                $charactersStaff
            )
        );
    }

    public function episodes(int $id, int $page = 1)
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
