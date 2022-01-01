<?php

namespace App\Http\Controllers\V4;

use Jikan\Helper\Constants;
use Jikan\Request\Watch\PopularEpisodesRequest;
use Jikan\Request\Watch\PopularPromotionalVideosRequest;
use Jikan\Request\Watch\RecentEpisodesRequest;
use Jikan\Request\Watch\RecentPromotionalVideosRequest;

class WatchController extends Controller
{

    public function recentEpisodes()
    {
        $results = $this->jikan->getRecentEpisodes(
            new RecentEpisodesRequest()
        );

        return response($this->serializer->serialize($results, 'json'));
    }

    public function popularEpisodes()
    {
        $results = $this->jikan->getPopularEpisodes(
            new PopularEpisodesRequest()
        );

        return response($this->serializer->serialize($results, 'json'));
    }

    public function recentPromos()
    {
        $page = $_GET['page'] ?? 1;
        $results = $this->jikan->getRecentPromotionalVideos(
            new RecentPromotionalVideosRequest($page)
        );

        return response($this->serializer->serialize($results, 'json'));
    }

    public function popularPromos()
    {
        $results = $this->jikan->getPopularPromotionalVideos(
            new PopularPromotionalVideosRequest()
        );

        return response($this->serializer->serialize($results, 'json'));
    }

}
