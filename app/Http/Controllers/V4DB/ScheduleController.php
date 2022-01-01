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
     *      @OA\Parameter(
     *          name="topic",
     *          in="path",
     *          required=false,
     *          description="Filter by day",
     *          @OA\Schema(type="string",enum={"monday", "tuesday", "wednesday", "thursday", "friday", "unknown", "other"})
     *      ),
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
     *          @OA\Schema(ref="#/components/schemas/pagination"),
     *          @OA\Schema(
     *              @OA\Property(
     *                   property="data",
     *                   type="array",
     *
     *                   @OA\Items(
     *                       allOf={
     *                           @OA\Schema(ref="#/components/schemas/anime"),
     *                       }
     *                   )
     *              ),
     *          )
     *      }
     *  )
     */
    public function main(Request $request, ?string $day = null)
    {
        $this->request = $request;

        $page = $this->request->get('page') ?? 1;
        $limit = $this->request->get('limit') ?? env('MAX_RESULTS_PER_PAGE', 25);

        if (!is_null($day)) {
            $this->day = strtolower($day);
        }

        if (null !== $this->day
            && !\in_array($this->day, self::VALID_FILTERS, true)) {
            return HttpResponse::badRequest($this->request);
        }

        $results = Anime::query()
            ->orderBy('members')
            ->where('type', 'TV')
            ->where('status', 'Currently Airing');

        if ($this->day !== null && in_array($day, self::VALID_DAYS)) {
            $this->day = ucfirst($this->day);

            $results
                ->where('broadcast', 'like', "{$day}%");
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
