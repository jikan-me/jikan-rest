<?php

namespace App\Http\Controllers\V4DB;

use App\Http\Resources\V4\InsightsCollection;
use App\Http\Resources\V4\TrendsCollection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\Regex;

class InsightsController extends Controller
{

    public function main(Request $request)
    {
        if (!env('INSIGHTS')) {
            return response()->json([
                'status' => 403,
                'type' => 'InsightsRuntimeException',
                'message' => 'Insights service is disabled',
                'error' => null
            ], 403);
        }

        $maxResultsPerPage = (int) env('MAX_RESULTS_PER_PAGE', 25);

        $page = $request->get('page') ?? 1;
        $limit = $request->get('limit') ?? $maxResultsPerPage;

        $limit = (int) $limit;

        if ($limit <= 0) {
            $limit = 1;
        }

        if ($limit > $maxResultsPerPage) {
            $limit = $maxResultsPerPage;
        }

        $results = DB::table('insights')
            ->where('timestamp', '>', time() - env('INSIGHTS_MAX_STORE_TIME', 172800) )
            ->orderBy('timestamp', 'desc')
            ->paginate(
                $limit,
                ['*'],
                null,
                $page
            );

        return new InsightsCollection(
            $results
        );
    }

    const TRENDS = [
        'anime',
        'manga',
        'people',
        'characters',
    ];

    public function trends(Request $request)
    {
        if (!env('INSIGHTS')) {
            return response()->json([
                'status' => 403,
                'type' => 'InsightsRuntimeException',
                'message' => 'Insights service is disabled',
                'error' => null
            ], 403);
        }

        $maxResultsPerPage = (int) env('MAX_RESULTS_PER_PAGE', 25);

        $page = $request->get('page') ?? 1;
        $limit = $request->get('limit') ?? $maxResultsPerPage;
        $trend = $request->get('trend') ?? null;

        $limit = (int) $limit;

        if ($limit <= 0) {
            $limit = 1;
        }

        if ($limit > $maxResultsPerPage) {
            $limit = $maxResultsPerPage;
        }

        if (is_null($trend) || !in_array($trend, self::TRENDS)) {
            return response()->json([
                'status' => 400,
                'type' => 'BadRequestException',
                'message' => 'Trend value is invalid',
                'error' => null
            ], 400);
        }

//        $results = DB::table('insights')
//            ->where('url', 'regexp',"/\/v(\d)\/{$trend}\/(\d+).*/i")
//            ->where('timestamp', '>', time() - env('INSIGHTS_MAX_STORE_TIME', 172800) )
//            ->orderBy('timestamp', 'desc')
//            ->paginate(
//                $limit,
//                ['*'],
//                null,
//                $page
//            );

        $results = DB::table('insights')
//            ->where('url', 'regexp',"/\/v(\d)\/{$trend}\/(\d+).*/i")
            ->where('timestamp', '>', time() - env('INSIGHTS_MAX_STORE_TIME', 172800) )
            ->orderBy('timestamp', 'desc')
            ->raw(fn($collection) => $collection->aggregate([
                [
                    '$group' => [
                        '_id' => [
                            'url' => '$url',
                            'timestamp' => '$timestamp'
                        ],
                        'urlCount' => [ '$sum' => 1 ]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$_id.url',
                        'count' => [ '$sum' => '$urlCount' ]
                    ]
                ],
                [
                    '$sort' => [ 'count' =>  -1 ]
                ],
                ['$skip' => ($page - 1) * $maxResultsPerPage],
                ['$limit' => $maxResultsPerPage],
            ]));
//            ->raw(fn($collection) => $collection->aggregate([
//                [
//                    '$group' => [
//                        '_id' => '$_id',
//                        'url' => ['$first' => '$url'],
//                        'count' => [ '$sum' => 1 ]
//                    ]
//                ]
//            ]));

        return new TrendsCollection(
            new LengthAwarePaginator(
                $results, DB::table('insights')->count(), $maxResultsPerPage, $page
            )
        );
    }


}
