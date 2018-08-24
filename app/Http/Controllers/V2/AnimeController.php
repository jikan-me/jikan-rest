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
        $anime = $this->_main($id);
        $episodes = $this->jikan->getAnimeEpisodes(new AnimeEpisodesRequest($id, $page));
        $episodes = json_decode(
            $this->serializer->serialize($episodes, 'json'),
            true
        );

        foreach ($episodes['episode'] as &$episode) {
            $episode['aired'] = $episode['aired']['string'];
        }


        return response(
            array_merge(
                $anime,
                $episodes
            )
        );
    }

    public function news(int $id)
    {
        $anime = $this->_main($id);
        $news = ['news' => $this->jikan->getNewsList(new AnimeNewsRequest($id))];
        $news = json_decode(
            $this->serializer->serialize($news, 'json'),
            true
        );


        return response(
            array_merge(
                $anime,
                $news
            )
        );
    }

    public function forum(int $id)
    {
        $anime = $this->_main($id);
        $forum = ['topic' => $this->jikan->getAnimeForum(new AnimeForumRequest($id))];
        $forum = json_decode(
            $this->serializer->serialize($forum, 'json'),
            true
        );


        return response(
            array_merge(
                $anime,
                $forum
            )
        );
    }

    public function videos(int $id)
    {
        $anime = $this->_main($id);
        $videos = $this->jikan->getAnimeVideos(new AnimeVideosRequest($id));
        $videos = json_decode(
            $this->serializer->serialize($videos, 'json'),
            true
        );


        return response(
            array_merge(
                $anime,
                $videos
            )
        );
    }

    public function pictures(int $id)
    {
        $anime = $this->_main($id);
        $pictures = ['image' =>$this->jikan->getAnimePictures(new AnimePicturesRequest($id))];
        $pictures = json_decode(
            $this->serializer->serialize($pictures, 'json'),
            true
        );

        foreach($pictures['image'] as $key => $value) {
            $pictures['image'][$key] = $value['small'];
        }


        return response(
            array_merge(
                $anime,
                $pictures
            )
        );
    }

    public function stats(int $id)
    {
        $anime = $this->_main($id);
        $stats = $this->jikan->getAnimeStats(new AnimeStatsRequest($id));
        $stats = json_decode(
            $this->serializer->serialize($stats, 'json'),
            true
        );

        return response(
            array_merge(
                $anime,
                $stats
            )
        );
    }

    public function moreInfo(int $id)
    {
        $anime = $this->_main($id);
        $moreinfo = ['moreinfo' => $this->jikan->getAnimeMoreInfo(new AnimeMoreInfoRequest($id))];
        $moreinfo = json_decode(
            $this->serializer->serialize($moreinfo, 'json'),
            true
        );

        return response(
            array_merge(
                $anime,
                $moreinfo
            )
        );
    }
}
