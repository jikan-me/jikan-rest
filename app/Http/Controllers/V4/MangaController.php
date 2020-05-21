<?php

namespace App\Http\Controllers\V4;

use App\Http\HttpHelper;
use Jikan\Request\Manga\MangaCharactersRequest;
use Jikan\Request\Manga\MangaForumRequest;
use Jikan\Request\Manga\MangaMoreInfoRequest;
use Jikan\Request\Manga\MangaNewsRequest;
use Jikan\Request\Manga\MangaPicturesRequest;
use Jikan\Request\Manga\MangaRecentlyUpdatedByUsersRequest;
use Jikan\Request\Manga\MangaRecommendationsRequest;
use Jikan\Request\Manga\MangaRequest;
use Jikan\Request\Manga\MangaReviewsRequest;
use Jikan\Request\Manga\MangaStatsRequest;

class MangaController extends Controller
{
    public function main(int $id)
    {
        $manga = $this->jikan->getManga(new MangaRequest($id));

        $mangaSerialized = $this->serializer->serialize($manga, 'json');
        $mangaSerialized = HttpHelper::serializeEmptyObjectsControllerLevel(
            json_decode($mangaSerialized, true)
        );

        return response($this->serializer->serialize($mangaSerialized, 'json'));
    }

    public function characters(int $id)
    {
        $manga = ['characters' => $this->jikan->getMangaCharacters(new MangaCharactersRequest($id))];
        return response($this->serializer->serialize($manga, 'json'));
    }

    public function news(int $id)
    {
        $manga = ['articles' => $this->jikan->getNewsList(new MangaNewsRequest($id))];
        return response($this->serializer->serialize($manga, 'json'));
    }

    public function forum(int $id)
    {
        $manga = ['topics' => $this->jikan->getMangaForum(new MangaForumRequest($id))];
        return response($this->serializer->serialize($manga, 'json'));
    }

    public function pictures(int $id)
    {
        $manga = ['pictures' => $this->jikan->getMangaPictures(new MangaPicturesRequest($id))];
        return response($this->serializer->serialize($manga, 'json'));
    }

    public function stats(int $id)
    {
        $manga = $this->jikan->getMangaStats(new MangaStatsRequest($id));
        return response($this->serializer->serialize($manga, 'json'));
    }

    public function moreInfo(int $id)
    {
        $manga = ['moreinfo' => $this->jikan->getMangaMoreInfo(new MangaMoreInfoRequest($id))];
        return response($this->serializer->serialize($manga, 'json'));
    }

    public function recommendations(int $id)
    {
        $manga = ['recommendations' => $this->jikan->getMangaRecommendations(new MangaRecommendationsRequest($id))];
        return response($this->serializer->serialize($manga, 'json'));
    }

    public function userupdates(int $id, int $page = 1)
    {
        $manga = ['users' => $this->jikan->getMangaRecentlyUpdatedByUsers(new MangaRecentlyUpdatedByUsersRequest($id, $page))];
        return response($this->serializer->serialize($manga, 'json'));
    }

    public function reviews(int $id, int $page = 1)
    {
        $manga = ['reviews' => $this->jikan->getMangaReviews(new MangaReviewsRequest($id, $page))];
        return response($this->serializer->serialize($manga, 'json'));
    }
}
