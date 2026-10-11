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
        if (!config('jikan.insights_enabled')) {
            return response()->json([
                'status' => 403,
                'type' => 'InsightsRuntimeException',
                'message' => 'Insights service is disabled',
                'error' => null
            ], 403);
        }

        $maxResultsPerPage = (int) config('jikan.max_results_per_page');

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
            ->where('timestamp', '>', time() - config('jikan.insights_max_store_time') )
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
        if (!config('jikan.insights_enabled')) {
            return response()->json([
                'status' => 403,
                'type' => 'InsightsRuntimeException',
                'message' => 'Insights service is disabled',
                'error' => null
            ], 403);
        }

        $maxResultsPerPage = (int) config('jikan.max_results_per_page');

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

$trendRegex = new Regex("/\/v\d+\/{$trend}\/\d+.*/i");
        $maxStoreTime = time() - config('jikan.insights_max_store_time');

        $results = DB::table('insights')
            ->where('timestamp', '>', $maxStoreTime)
            ->raw(fn($collection) => $collection->aggregate([
                [
                    '$match' => [
                        'url' => $trendRegex,
                        'timestamp' => ['$gt' => $maxStoreTime],
                    ],
                ],
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
                ['$skip' => ($page - 1) * $limit],
                ['$limit' => $limit],
            ]));

        $totalItems = DB::table('insights')
            ->whereRaw([
                'url' => $trendRegex,
                'timestamp' => ['$gt' => $maxStoreTime],
            ])
            ->count();

        return new TrendsCollection(
            new LengthAwarePaginator($results, $totalItems, $limit, $page)
        );
    }


}
