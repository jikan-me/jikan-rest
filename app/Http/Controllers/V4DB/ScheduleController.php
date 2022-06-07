<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Http\HttpResponse;
use App\Http\Resources\V4\AnimeCollection;
use App\Http\Resources\V4\AnimeResource;
use App\Http\Resources\V4\CommonResource;
use App\Http\Resources\V4\ScheduleResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\Helper\Constants;
use Jikan\Request\Schedule\ScheduleRequest;

class ScheduleController extends Controller
{
    private const VALID_FILTERS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
        'other',
        'unknown',
    ];

    private const VALID_DAYS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    private $request;
    private $day;

    /**
     *  @OA\Get(
     *     path="/schedules",
     *     operationId="getSchedules",
     *     tags={"schedules"},
     *
     *      @OA\Parameter(ref="#/components/parameters/page"),
     *
     *      @OA\Parameter(
     *          name="filter",
     *          in="query",
     *          required=false,
     *          description="Filter by day",
     *          @OA\Schema(type="string",enum={"monday", "tuesday", "wednesday", "thursday", "friday", "unknown", "other"})
     *      ),
     *
     *      @OA\Parameter(
     *          name="kids",
     *          in="query",
     *          required=false,
     *          description="When supplied, it will filter entries with the `Kids` Genre Demographic. When supplied as `kids=true`, it will return only Kid entries and when supplied as `kids=false`, it will filter out any Kid entries. Defaults to `false`.",
     *          @OA\Schema(type="string",enum={"true", "false"})
     *      ),
     *
     *      @OA\Parameter(
     *          name="sfw",
     *          in="query",
     *          required=false,
     *          description="'Safe For Work'. When supplied, it will filter entries with the `Hentai` Genre. When supplied as `sfw=true`, it will return only SFW entries and when supplied as `sfw=false`, it will filter out any Hentai entries. Defaults to `false`.",
     *          @OA\Schema(type="string",enum={"true", "false"})
     *      ),
     *
     *     @OA\Parameter(ref="#/components/parameters/limit"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns weekly schedule",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/schedules",
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     *
     *  @OA\Schema(
     *      schema="schedules",
     *      description="Anime resources currently airing",
     *
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/pagination_plus"),
     *          @OA\Schema(
     *              @OA\Property(
     *                   property="data",
     *                   type="array",
     *
     *                   @OA\Items(
     *                       type="object",
     *                       ref="#/components/schemas/anime",
     *                   )
     *              ),
     *          )
     *      }
     *  )
     */
    /*
     * all have status as currently airing
     * all have premiered but they're not necesarily the current season or year
     * all have aired date but they're not necessarily the current date/season
     *
     */
    public function main(Request $request, ?string $day = null)
    {
        $this->request = $request;

        $page = $this->request->get('page') ?? 1;
        $limit = $this->request->get('limit') ?? env('MAX_RESULTS_PER_PAGE', 25);
        $filter = $this->request->get('filter') ?? null;
        $kids = $this->request->get('kids') ?? false;
        $sfw = $this->request->get('sfw') ?? false;

        if (!is_null($day)) {
            $this->day = strtolower($day);
        }

        if (!is_null($filter) && is_null($day)) {
            $this->day = strtolower($filter);
        }

        if (null !== $this->day
            && !\in_array($this->day, self::VALID_FILTERS, true)) {
            return HttpResponse::badRequest($this->request);
        }

        $results = Anime::query()
            ->orderBy('members')
            ->where('type', 'TV')
            ->where('status', 'Currently Airing')
        ;

        if ($kids) {
            $results = $results
                ->orWhere('demographics.mal_id', '!=', Constants::GENRE_ANIME_KIDS);
        }

        if ($sfw) {
            $results = $results
                ->orWhere('demographics.mal_id', '!=', Constants::GENRE_ANIME_HENTAI);
        }

//        if (is_null($sfw)) {
//            $results = $results
//                ->orWhere('genres.mal_id', '!=', Constants::GENRE_ANIME_HENTAI)
//                ->orWhere('genres.mal_id', '!=', Constants::GENRE_ANIME_BOYS_LOVE)
//                ->orWhere('genres.mal_id', '!=', Constants::GENRE_ANIME_GIRLS_LOVE)
//                ;
//        }

        if (\in_array($this->day, self::VALID_DAYS)) {
            $this->day = ucfirst($this->day);

            $results
                ->where('broadcast', 'like', "{$this->day}%");
        }

        if ($this->day === 'unknown') {
            $results
                ->where('broadcast', 'Unknown');
        }

        if ($this->day === 'other') {
            $results
                ->where('broadcast', 'Not scheduled once per week');
        }

        $results = $results
            ->paginate(
                intval($limit),
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
}
