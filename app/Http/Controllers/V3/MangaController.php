<?php

namespace App\Http\Controllers\V3;

use App\Http\HttpHelper;
use App\Http\HttpResponse;
use App\Manga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\Request\Manga\MangaCharactersRequest;
use Jikan\Request\Manga\MangaForumRequest;
use Jikan\Request\Manga\MangaMoreInfoRequest;
use Jikan\Request\Manga\MangaNewsRequest;
use Jikan\Request\Manga\MangaPicturesRequest;
use Jikan\Request\Manga\MangaRecentlyUpdatedByUsersRequest;
use Jikan\Request\Manga\MangaRecommendationsRequest;
use Jikan\Request\Manga\MangaReviewsRequest;
use Jikan\Request\Manga\MangaStatsRequest;
use MongoDB\BSON\UTCDateTime;

class MangaController extends Controller
{
    public function main(Request $request, int $id)
    {
        $results = Manga::query()
            ->where('mal_id', $id)
            ->get();

        $isExpired = $this->isExpired($request, $results);
        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;

        if (
            $results->isEmpty()
            || $isExpired
        ) {
            $response = Manga::scrape($id);

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
                Manga::query()
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                Manga::query()
                    ->where('request_hash', $this->fingerprint)
                    ->update($response);
            }

            $results = Manga::query()
                ->where('mal_id', $id)
                ->get();
        }


        if ($results->isEmpty()) {
            return HttpResponse::notFound($request);
        }

        $response = (new \App\Http\Resources\V3\MangaResource(
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

    public function characters(Request $request, int $id)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        $isExpired = $this->isExpired($request, $results);
        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;

        if (
            $results->isEmpty()
            || $isExpired
        ) {
            $manga = ['characters' => $this->jikan->getMangaCharacters(new MangaCharactersRequest($id))];
            $response = \json_decode($this->serializer->serialize($manga, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new \App\Http\Resources\V3\MangaCharactersResource(
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

        $isExpired = $this->isExpired($request, $results);
        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;

        if (
            $results->isEmpty()
            || $isExpired
        ) {
            $page = $request->get('page') ?? 1;
            $manga = $this->jikan->getNewsList(new MangaNewsRequest($id, $page));
            $response = \json_decode($this->serializer->serialize($manga, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new \App\Http\Resources\V3\MangaNewsResource(
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
        // safely bypass MAL's naming schemes
        if ($topic === 'chapters') {
            $topic = 'chapter';
        }

        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        $isExpired = $this->isExpired($request, $results);
        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;

        if (
            $results->isEmpty()
            || $isExpired
        ) {
            $topic = $request->get('topic');
            $manga = ['topics' => $this->jikan->getMangaForum(new MangaForumRequest($id))];
            $response = \json_decode($this->serializer->serialize($manga, 'json'), true);

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

    public function pictures(Request $request, int $id)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        $isExpired = $this->isExpired($request, $results);
        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;

        if (
            $results->isEmpty()
            || $isExpired
        ) {
            $manga = ['pictures' => $this->jikan->getMangaPictures(new MangaPicturesRequest($id))];
            $response = \json_decode($this->serializer->serialize($manga, 'json'), true);

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

        $isExpired = $this->isExpired($request, $results);
        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;

        if (
            $results->isEmpty()
            || $isExpired
        ) {
            $manga = $this->jikan->getMangaStats(new MangaStatsRequest($id));
            $response = \json_decode($this->serializer->serialize($manga, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new \App\Http\Resources\V3\MangaStatisticsResource(
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

        $isExpired = $this->isExpired($request, $results);
        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;

        if (
            $results->isEmpty()
            || $isExpired
        ) {
            $manga = ['moreinfo' => $this->jikan->getMangaMoreInfo(new MangaMoreInfoRequest($id))];
            $response = \json_decode($this->serializer->serialize($manga, 'json'), true);

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

        $isExpired = $this->isExpired($request, $results);
        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;

        if (
            $results->isEmpty()
            || $isExpired
        ) {
            $manga = ['recommendations' => $this->jikan->getMangaRecommendations(new MangaRecommendationsRequest($id))];
            $response = \json_decode($this->serializer->serialize($manga, 'json'), true);

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

        $isExpired = $this->isExpired($request, $results);
        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;

        if (
            $results->isEmpty()
            || $isExpired
        ) {
            $page = $request->get('page') ?? 1;
            $manga = ['users' => $this->jikan->getMangaRecentlyUpdatedByUsers(new MangaRecentlyUpdatedByUsersRequest($id, $page))];
            $response = \json_decode($this->serializer->serialize($manga, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new \App\Http\Resources\V3\MangaUserUpdatesResource(
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

        $isExpired = $this->isExpired($request, $results);
        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;

        if (
            $results->isEmpty()
            || $isExpired
        ) {
            $page = $request->get('page') ?? 1;
            $manga = $this->jikan->getMangaReviews(new MangaReviewsRequest($id, $page));
            $response = \json_decode($this->serializer->serialize($manga, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new \App\Http\Resources\V3\MangaReviewsResource(
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
