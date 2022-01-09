<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Http\HttpResponse;
use App\Http\QueryBuilder\SearchQueryBuilderAnime;
use App\Http\Resources\V4\AnimeCollection;
use App\Http\Resources\V4\ResultsResource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Jikan\Request\Seasonal\SeasonalRequest;
use Jikan\Request\SeasonList\SeasonListRequest;
use Jikan\Request\Watch\PopularPromotionalVideosRequest;

class SeasonController extends Controller
{
    private const VALID_SEASONS = [
        'Summer',
        'Spring',
        'Winter',
        'Fall'
    ];

    /**
     * @OA\Get(
     *     path="/seasons/{year}/{season}",
     *     operationId="getSeason",
     *     tags={"seasons"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns seasonal anime",
     *          @OA\JsonContent(
     *               ref="#/components/schemas/anime search"
     *          )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     *
     * @OA\Get(
     *     path="/seasons/now",
     *     operationId="getSeasonNow",
     *     tags={"seasons"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns current seasonal anime",
     *          @OA\JsonContent(
     *               ref="#/components/schemas/anime search"
     *          )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     * @throws Exception
     */
    public function main(Request $request, ?int $year = null, ?string $season = null)
    {
        $maxResultsPerPage = env('MAX_RESULTS_PER_PAGE', 30);
        $page = $request->get('page') ?? 1;
        $limit = $request->get('limit') ?? $maxResultsPerPage;

        if (!empty($limit)) {
            $limit = (int) $limit;

            if ($limit <= 0) {
                $limit = 1;
            }

            if ($limit > $maxResultsPerPage) {
                $limit = $maxResultsPerPage;
            }
        }

        if (!is_null($season)) {
            $season = ucfirst(
                strtolower($season)
            );
        }

        if (!is_null($year)) {
            $year = (int) $year;
        }

        if (!is_null($season)
        && !\in_array($season, self::VALID_SEASONS)) {
            return HttpResponse::badRequest($request);
        }

        if (is_null($season) && is_null($year)) {
            list($season, $year) = $this->getSeasonStr();
        }

        $results = Anime::query()
            ->where('premiered', "{$season} $year")
            ->orderBy('members', 'desc')
            ->paginate(
                $limit,
                ['*'],
                null,
                $page);

        $response = (new AnimeCollection(
            $results
        ))->response();

        return $this->prepareResponse(
            $response,
            $results,
            $request
        );
    }

    /**
     *  @OA\Get(
     *     path="/seasons",
     *     operationId="getSeasonsList",
     *     tags={"seasons"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns available list of seasons",
     *          @OA\JsonContent(
     *               ref="#/components/schemas/seasons"
     *          )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     *
     * @OA\Schema(
     *     schema="seasons",
     *     description="List of available seasons",
     *
     *      @OA\Property(
     *          property="data",
     *          type="array",
     *
     *          @OA\Items(
     *              type="object",
     *              @OA\Property(
     *                   property="year",
     *                   type="integer",
     *                   description="Year"
     *              ),
     *              @OA\Property(
     *                   property="seasons",
     *                   type="array",
     *                   description="List of available seasons",
     *                   @OA\Items(
     *                       type="string"
     *                   ),
     *              ),
     *          ),
     *      ),
     * ),
     */
    public function archive(Request $request)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $items = $this->jikan->getSeasonList(new SeasonListRequest());
            $response = \json_decode($this->serializer->serialize($items, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new ResultsResource(
            $results->first()
        ))->response();

        return $this->prepareResponse(
            $response,
            $results,
            $request
        );
    }

    /**
     * @OA\Get(
     *     path="/seasons/upcoming",
     *     operationId="getSeasonUpcoming",
     *     tags={"seasons"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns upcoming season's anime",
     *          @OA\JsonContent(
     *               ref="#/components/schemas/anime search"
     *          )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     * @throws Exception
     */
    public function later(Request $request)
    {
        $maxResultsPerPage = env('MAX_RESULTS_PER_PAGE', 30);
        $page = $request->get('page') ?? 1;
        $limit = $request->get('limit') ?? $maxResultsPerPage;

        if (!empty($limit)) {
            $limit = (int) $limit;

            if ($limit <= 0) {
                $limit = 1;
            }

            if ($limit > $maxResultsPerPage) {
                $limit = $maxResultsPerPage;
            }
        }


        //        $nextYear =   (new \DateTime(null, new \DateTimeZone('Asia/Tokyo')))
//            ->modify('+1 year')
//            ->format('Y');

        $results = Anime::query()
            ->where('status', 'Not yet aired')
//            ->where('premiered', 'like', "%{$nextYear}%")
            ->orderBy('members', 'desc');

        $season = 'Later';


        $results = $results
            ->paginate(
                $limit,
                ['*'],
                null,
                $page
            );

        $response = (new AnimeCollection(
            $results
        ))->response();

        return $this->prepareResponse(
            $response,
            $results,
            $request
        );
    }


    /**
     * @return array
     * @throws Exception
     */
    private function getSeasonStr() : array
    {
        $date = new \DateTime(null, new \DateTimeZone('Asia/Tokyo'));

        $year = (int) $date->format('Y');
        $month = (int) $date->format('n');

        switch ($month) {
            case \in_array($month, range(1, 3)):
                return ['Winter', $year];
            case \in_array($month, range(4, 6)):
                return ['Spring', $year];
            case \in_array($month, range(7, 9)):
                return ['Summer', $year];
            case \in_array($month, range(10, 12)):
                return ['Fall', $year];
            default: throw new Exception('Could not generate seasonal string');
        }
    }
}
