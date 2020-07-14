<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Http\HttpHelper;
use App\Http\HttpResponse;
use App\Http\QueryBuilder\SearchQueryBuilderAnime;
use App\Http\QueryBuilder\SearchQueryBuilderMagazine;
use App\Http\Resources\V4\AnimeCollection;
use App\Http\Resources\V4\MagazineCollection;
use App\Http\Resources\V4\NewsResource;
use App\Magazine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Eloquent\Builder;
use Jikan\Request\Anime\AnimeNewsRequest;
use Jikan\Request\Magazine\MagazineRequest;
use Jikan\Request\Magazine\MagazinesRequest;
use MongoDB\BSON\UTCDateTime;

class MagazineController extends Controller
{

    const MAX_RESULTS_PER_PAGE = 25;

    public function main(Request $request)
    {
/*
        // shift to queue job and inital indexing, only query for now

        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $response = Magazine::scrape();

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
                DB::table($this->getRouteTable($request))
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                DB::table($this->getRouteTable($request))
                    ->where('request_hash', $this->fingerprint)
                    ->update($response);
            }

            $results = DB::table($this->getRouteTable($request))
                ->where('request_hash', $this->fingerprint)
                ->get();
        }



        if (
            $this->isExpired($request, $results)
        ) {
            $results = $results->first()['magazines'];

            // todo implement DB transaction when added to `jenssegers/laravel-mongodb`
            foreach ($results as $magazine) {
                Magazine::query()
                    ->where('mal_id', (int)$magazine['mal_id'])
                    ->updateOrCreate($magazine);
            }
        }*/

        $page = $request->get('page') ?? 1;
        $limit = $request->get('limit') ?? self::MAX_RESULTS_PER_PAGE;

        if (!empty($limit)) {
            $limit = (int) $limit;

            if ($limit <= 0) {
                $limit = 1;
            }

            if ($limit > self::MAX_RESULTS_PER_PAGE) {
                $limit = self::MAX_RESULTS_PER_PAGE;
            }
        }

        $results = SearchQueryBuilderMagazine::query(
            $request,
            Magazine::query()
        );

        $results = $results
            ->paginate(
                $limit,
                ['*'],
                null,
                $page
            );

        return new MagazineCollection(
            $results
        );
    }

    public function resource(int $id, int $page = 1)
    {
        $magazine = $this->jikan->getMagazine(new MagazineRequest($id, $page));
        return response($this->serializer->serialize($magazine, 'json'));
    }
}
