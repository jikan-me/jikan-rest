<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Http\HttpHelper;
use App\Http\HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\Request\Anime\AnimeCharactersAndStaffRequest;
use Jikan\Request\Anime\AnimeEpisodeRequest;
use Jikan\Request\Anime\AnimeEpisodesRequest;
use Jikan\Request\Anime\AnimeForumRequest;
use Jikan\Request\Anime\AnimeMoreInfoRequest;
use Jikan\Request\Anime\AnimeNewsRequest;
use Jikan\Request\Anime\AnimePicturesRequest;
use Jikan\Request\Anime\AnimeRecentlyUpdatedByUsersRequest;
use Jikan\Request\Anime\AnimeRecommendationsRequest;
use Jikan\Request\Anime\AnimeRequest;
use Jikan\Request\Anime\AnimeReviewsRequest;
use Jikan\Request\Anime\AnimeStatsRequest;
use Jikan\Request\Anime\AnimeVideosRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AnimeController extends Controller
{
    public function main(Request $request, int $id)
    {

        return new \App\Http\Resources\V4\AnimeResource(
            Anime::query()
                ->where('mal_id', $id)
                ->get([
                    'mal_id','url','image_url','trailer_url','title','title_english','title_japanese','title_synonyms','type','source','episodes','status','airing','aired','duration','rating','score','scored_by','rank','popularity','members','favorites','synopsis','background','premiered','broadcast','related','producers','licensors','studios','genres','opening_themes','ending_themes'
                ])
                ->first()
        );

        return response()->json($result);

        $result = DB::table('anime')
            ->where('mal_id', $id)
            ->get([
                'mal_id','url','image_url','trailer_url','title','title_english','title_japanese','title_synonyms','type','source','episodes','status','airing','aired','duration','rating','score','scored_by','rank','popularity','members','favorites','synopsis','background','premiered','broadcast','related','producers','licensors','studios','genres','opening_themes','ending_themes'
            ]);

        $response = $result->toArray();

        if (!empty($response)) {
            $response = $response[0];

            return response(
                $this->prepareResponse($request, $response)
            );
        }

        return HttpResponse::notFound($request);
    }

    public function characters_staff(int $id)
    {
        $anime = $this->jikan->getAnimeCharactersAndStaff(new AnimeCharactersAndStaffRequest($id));
        return response($this->serializer->serialize($anime, 'json'));
    }

    public function episode(int $id, int $episodeId)
    {
        $anime = $this->jikan->getAnimeEpisode(new AnimeEpisodeRequest($id, $episodeId));
        return response($this->serializer->serialize($anime, 'json'));
    }

    public function episodes(int $id)
    {
        $page = $_GET['page'] ?? 1;
        $anime = $this->jikan->getAnimeEpisodes(new AnimeEpisodesRequest($id, $page));
        return response($this->serializer->serialize($anime, 'json'));
    }

    public function news(int $id)
    {
        $anime = ['articles' => $this->jikan->getNewsList(new AnimeNewsRequest($id))];
        return response($this->serializer->serialize($anime, 'json'));
    }

    public function forum(int $id)
    {
        $anime = ['topics' => $this->jikan->getAnimeForum(new AnimeForumRequest($id))];
        return response($this->serializer->serialize($anime, 'json'));
    }

    public function videos(int $id)
    {
        $anime = $this->jikan->getAnimeVideos(new AnimeVideosRequest($id));
        return response($this->serializer->serialize($anime, 'json'));
    }

    public function pictures(int $id)
    {
        $anime = ['pictures' => $this->jikan->getAnimePictures(new AnimePicturesRequest($id))];
        return response($this->serializer->serialize($anime, 'json'));
    }

    public function stats(int $id)
    {
        $anime = $this->jikan->getAnimeStats(new AnimeStatsRequest($id));
        return response($this->serializer->serialize($anime, 'json'));
    }

    public function moreInfo(int $id)
    {
        $anime = ['moreinfo' => $this->jikan->getAnimeMoreInfo(new AnimeMoreInfoRequest($id))];
        return response(json_encode($anime));
    }

    public function recommendations(int $id)
    {
        $anime = ['recommendations' => $this->jikan->getAnimeRecommendations(new AnimeRecommendationsRequest($id))];
        return response($this->serializer->serialize($anime, 'json'));
    }

    public function userupdates(int $id, int $page = 1)
    {
        $anime = ['users' => $this->jikan->getAnimeRecentlyUpdatedByUsers(new AnimeRecentlyUpdatedByUsersRequest($id, $page))];
        return response($this->serializer->serialize($anime, 'json'));
    }

    public function reviews(int $id, int $page = 1)
    {
        $anime = ['reviews' => $this->jikan->getAnimeReviews(new AnimeReviewsRequest($id, $page))];
        return response($this->serializer->serialize($anime, 'json'));
    }
}
