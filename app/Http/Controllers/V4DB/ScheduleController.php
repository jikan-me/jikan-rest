<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\QueryAnimeSchedulesCommand;

class ScheduleController extends Controller
{
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
    public function main(QueryAnimeSchedulesCommand $command)
    {
        return $this->mediator->send($command);
    }

//    public function byDay(QueryAnimeSchedulesByDayCommand $command)
//    {
//        return $this->mediator->send($command);
//    }
}
