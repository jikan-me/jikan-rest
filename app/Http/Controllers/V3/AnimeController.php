<?php

namespace App\Http\Controllers\V3;

use App\Anime;
use App\Http\HttpHelper;
use App\Http\HttpResponse;
use App\Http\Resources\V3\AnimeEpisodesResource;
use App\Http\Resources\V4\AnimeCharactersResource;
use App\Http\Resources\V4\AnimeStatisticsResource;
use App\Http\Resources\V4\AnimeVideosResource;
use App\Http\Resources\V4\ForumResource;
use App\Http\Resources\V4\MoreInfoResource;
use App\Http\Resources\V4\PicturesResource;
use App\Http\Resources\V4\RecommendationsResource;
use App\Http\Resources\V4\ResultsResource;
use App\Http\Resources\V4\ReviewsResource;
use App\Http\Resources\V4\UserUpdatesResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\Request\Anime\AnimeCharactersAndStaffRequest;
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
use MongoDB\BSON\UTCDateTime;

class AnimeController extends Controller
{
    public function main(Request $request, int $id)
    {
        $results = Anime::query()
            ->where('mal_id', $id)
            ->get();

        $isExpired = $this->isExpired($request, $results);
        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;

        if (
            $results->isEmpty()
            || $isExpired
        ) {
            $cached = false;
            $response = Anime::scrape($id);

            if (HttpHelper::hasError($response)) {
                return HttpResponse::notFound($request);
            }

            if ($results->isEmpty()) {
                $meta = [
                    'createdAt' => new UTCDateTime(),
                    'modifiedAt' => new UTCDateTime(),
                    'request_hash' => $this->fingerprint
                ];
            }
            $meta['modifiedAt'] = new UTCDateTime();

            $response = $meta + $response;

            if ($results->isEmpty()) {
                Anime::query()
                    ->insert($response);
            }

            if ($isExpired) {
                Anime::query()
                    ->where('request_hash', $this->fingerprint)
                    ->update($response);
            }

            $results = Anime::query()
                ->where('mal_id', $id)
                ->get();
        }

        if ($results->isEmpty()) {
            return HttpResponse::notFound($request);
        }

        $response = (new \App\Http\Resources\V3\AnimeResource(
            $results->first()
        ))->toArray($request);

        return $this->prepareResponse(
            response(
                $this->bcMeta($response, $this->fingerprint, $cached, $expiryTtl)
            ),
            $results,
            $request
        );
    }

    public function characters_staff(Request $request, int $id)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        $isExpired = $this->isExpired($request, $results);
        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $anime = $this->jikan->getAnimeCharactersAndStaff(new AnimeCharactersAndStaffRequest($id));
            $response = \json_decode($this->serializer->serialize($anime, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new \App\Http\Resources\V3\AnimeCharactersStaffResource(
            $results->first()
        ))->toArray($request);

        return $this->prepareResponse(
            response(
                $this->bcMeta($response, $this->fingerprint, $cached, $expiryTtl)
            ),
            $results,
            $request
        );
    }

    public function episodes(Request $request, int $id, int $page = 1)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $page = $request->get('page') ?? 1;
            $anime = $this->jikan->getAnimeEpisodes(new AnimeEpisodesRequest($id, $page));
            $response = \json_decode($this->serializer->serialize($anime, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new \App\Http\Resources\V3\AnimeEpisodesResource(
            $results->first()
        ))->toArray($request);

        return $this->prepareResponse(
            response(
                $this->bcMeta($response, $this->fingerprint, $cached, $expiryTtl)
            ),
            $results,
            $request
        );
    }

    public function news(Request $request, int $id)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $page = $request->get('page') ?? 1;
            $anime = $this->jikan->getNewsList(new AnimeNewsRequest($id, $page));
            $response = \json_decode($this->serializer->serialize($anime, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new \App\Http\Resources\V3\AnimeNewsResource(
            $results->first()
        ))->toArray($request);

        return $this->prepareResponse(
            response(
                $this->bcMeta($response, $this->fingerprint, $cached, $expiryTtl)
            ),
            $results,
            $request
        );
    }

    public function forum(Request $request, int $id, ?string $topic = null)
    {
        if ($topic === 'episodes') {
            $topic = 'episode';
        }

        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $topic = $request->get('topic');
            $anime = ['topics' => $this->jikan->getAnimeForum(new AnimeForumRequest($id, $topic))];
            $response = \json_decode($this->serializer->serialize($anime, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new \App\Http\Resources\V3\ForumResource(
            $results->first()
        ))->toArray($request);

        return $this->prepareResponse(
            response(
                $this->bcMeta($response, $this->fingerprint, $cached, $expiryTtl)
            ),
            $results,
            $request
        );
    }

    public function videos(Request $request, int $id)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $anime = $this->jikan->getAnimeVideos(new AnimeVideosRequest($id));
            $response = \json_decode($this->serializer->serialize($anime, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new \App\Http\Resources\V3\AnimeVideosResource(
            $results->first()
        ))->toArray($request);

        return $this->prepareResponse(
            response(
                $this->bcMeta($response, $this->fingerprint, $cached, $expiryTtl)
            ),
            $results,
            $request
        );
    }

    public function pictures(Request $request, int $id)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $anime = ['pictures' => $this->jikan->getAnimePictures(new AnimePicturesRequest($id))];
            $response = \json_decode($this->serializer->serialize($anime, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new \App\Http\Resources\V3\PicturesResource(
            $results->first()
        ))->toArray($request);

        return $this->prepareResponse(
            response(
                $this->bcMeta($response, $this->fingerprint, $cached, $expiryTtl)
            ),
            $results,
            $request
        );
    }

    public function stats(Request $request, int $id)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $anime = $this->jikan->getAnimeStats(new AnimeStatsRequest($id));
            $response = \json_decode($this->serializer->serialize($anime, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new \App\Http\Resources\V3\AnimeStatisticsResource(
            $results->first()
        ))->toArray($request);

        return $this->prepareResponse(
            response(
                $this->bcMeta($response, $this->fingerprint, $cached, $expiryTtl)
            ),
            $results,
            $request
        );
    }

    public function moreInfo(Request $request, int $id)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $anime = ['moreinfo' => $this->jikan->getAnimeMoreInfo(new AnimeMoreInfoRequest($id))];
            $response = \json_decode($this->serializer->serialize($anime, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new \App\Http\Resources\V3\MoreInfoResource(
            $results->first()
        ))->toArray($request);

        return $this->prepareResponse(
            response(
                $this->bcMeta($response, $this->fingerprint, $cached, $expiryTtl)
            ),
            $results,
            $request
        );
    }

    public function recommendations(Request $request, int $id)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $anime = ['recommendations' => $this->jikan->getAnimeRecommendations(new AnimeRecommendationsRequest($id))];
            $response = \json_decode($this->serializer->serialize($anime, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new \App\Http\Resources\V3\RecommendationsResource(
            $results->first()
        ))->toArray($request);

        return $this->prepareResponse(
            response(
                $this->bcMeta($response, $this->fingerprint, $cached, $expiryTtl)
            ),
            $results,
            $request
        );
    }

    public function userupdates(Request $request, int $id, int $page = 1)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $page = $request->get('page') ?? 1;
            $anime = ['users' => $this->jikan->getAnimeRecentlyUpdatedByUsers(new AnimeRecentlyUpdatedByUsersRequest($id, $page))];
            $response = \json_decode($this->serializer->serialize($anime, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new \App\Http\Resources\V3\AnimeUserUpdatesResource(
            $results->first()
        ))->toArray($request);

        return $this->prepareResponse(
            response(
                $this->bcMeta($response, $this->fingerprint, $cached, $expiryTtl)
            ),
            $results,
            $request
        );
    }

    public function reviews(Request $request, int $id, int $page = 1)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $page = $request->get('page') ?? 1;
            $anime = $this->jikan->getAnimeReviews(new AnimeReviewsRequest($id, $page));
            $response = \json_decode($this->serializer->serialize($anime, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new \App\Http\Resources\V3\AnimeReviewsResource(
            $results->first()
        ))->toArray($request);

        return $this->prepareResponse(
            response(
                $this->bcMeta($response, $this->fingerprint, $cached, $expiryTtl)
            ),
            $results,
            $request
        );
    }
}
