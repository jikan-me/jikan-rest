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
     *     @OA\Response(
     *         response="200",
     *         description="Returns weekly schedule",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     *
     * @OA\Schema(
     *     schema="schedules",
     *     description="List of weekly schedule",
     *
     *      @OA\Property(
     *          property="data",
     *          type="object",
     *          @OA\Property(
     *              property="monday",
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  ref="#/components/schemas/anime",
     *              ),
     *          ),
     *          @OA\Property(
     *              property="tuesday",
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  ref="#/components/schemas/anime",
     *              ),
     *          ),
     *          @OA\Property(
     *              property="wednesday",
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  ref="#/components/schemas/anime",
     *              ),
     *          ),
     *          @OA\Property(
     *              property="thursday",
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  ref="#/components/schemas/anime",
     *              ),
     *          ),
     *          @OA\Property(
     *              property="friday",
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  ref="#/components/schemas/anime",
     *              ),
     *          ),
     *          @OA\Property(
     *              property="saturday",
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  ref="#/components/schemas/anime",
     *              ),
     *          ),
     *          @OA\Property(
     *              property="sunday",
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  ref="#/components/schemas/anime",
     *              ),
     *          ),
     *          @OA\Property(
     *              property="other",
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  ref="#/components/schemas/anime",
     *              ),
     *          ),
     *          @OA\Property(
     *              property="unknown",
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  ref="#/components/schemas/anime",
     *              ),
     *          ),
     *      ),
     * ),
     */
    public function main(Request $request, ?string $day = null)
    {
        $this->request = $request;

        $page = $this->request->get('page') ?? 1;
        $limit = $this->request->get('limit') ?? (int) env('MAX_RESULTS_PER_PAGE', 25);

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
}
