<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Http\HttpResponse;
use App\Http\Resources\V4\AnimeCollection;
use App\Http\Resources\V4\CommonResource;
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
            ->where('status', 'Currently Airing')
            ->get();

        $results = $this->mutateQueryResponse($results);

        if (!is_null($this->day)) {
            $results = [$this->day => new AnimeCollection($results[$this->day])];
        }

        return new CommonResource($results);
    }

    private function mutateQueryResponse($results)
    {
        $return = [
            'monday' => [],
            'tuesday' => [],
            'wednesday' => [],
            'thursday' => [],
            'friday' => [],
            'saturday' => [],
            'sunday' => [],
            'other' => [],
            'unknown' => []
        ];

        $items = $results->toArray() ?? [];
        foreach ($items as $item) {

            if ($item['broadcast']['string'] === 'Unknown') {
                $return['unknown'][] = $item;
            }

            if ($item['broadcast']['string'] === 'Not scheduled once per week') {
                $return['other'][] = $item;
            }

            foreach (self::VALID_FILTERS as $day) {
                $broadcastDay = ucfirst($day);

                if (preg_match("~^{$broadcastDay}~", $item['broadcast']['day'])) {
                    $return[$day][] = $item;
                }
            }
        }

        foreach ($return as &$day) {
            $day = array_reverse($day);
        }

        if (!is_null($this->day)) {
            $return = [$this->day => $return[$this->day]];
        }

        return $return;
    }
}
