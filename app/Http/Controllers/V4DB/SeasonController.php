<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Dto\QueryAnimeSeasonListCommand;
use App\Dto\QueryCurrentAnimeSeasonCommand;
use App\Dto\QuerySpecificAnimeSeasonCommand;
use App\Dto\QueryUpcomingAnimeSeasonCommand;
use App\Http\HttpResponse;
use App\Http\QueryBuilder\AnimeSearchQueryBuilder;
use App\Http\Resources\V4\AnimeCollection;
use App\Http\Resources\V4\ResultsResource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Jikan\Request\SeasonList\SeasonListRequest;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

/**
 *
 */
class SeasonController extends Controller
{
    /**
     * @OA\Get(
     *     path="/seasons/now",
     *     operationId="getSeasonNow",
     *     tags={"seasons"},
     *
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *     @OA\Parameter(ref="#/components/parameters/limit"),
     *     @OA\Parameter(
     *       name="filter",
     *       description="Entry types",
     *       in="query",
     *       @OA\Schema(type="string",enum={"tv","movie","ova","special","ona","music"})
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns current seasonal anime",
     *          @OA\JsonContent(
     *               ref="#/components/schemas/anime_search"
     *          )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     * @throws Exception
     */
    public function now(QueryCurrentAnimeSeasonCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     * @OA\Get(
     *     path="/seasons/{year}/{season}",
     *     operationId="getSeason",
     *     tags={"seasons"},
     *
     *     @OA\Parameter(
     *       name="year",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *       name="season",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *       name="filter",
     *       description="Entry types",
     *       in="query",
     *       @OA\Schema(type="string",enum={"tv","movie","ova","special","ona","music"})
     *     ),
     *
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *     @OA\Parameter(ref="#/components/parameters/limit"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns seasonal anime",
     *          @OA\JsonContent(
     *               ref="#/components/schemas/anime_search"
     *          )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     * @throws Exception
     */
    public function main(QuerySpecificAnimeSeasonCommand $command)
    {
        return $this->mediator->send($command);
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
    public function archive(QueryAnimeSeasonListCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     * @OA\Get(
     *     path="/seasons/upcoming",
     *     operationId="getSeasonUpcoming",
     *     tags={"seasons"},
     *
     *     @OA\Parameter(
     *       name="filter",
     *       description="Entry types",
     *       in="query",
     *       @OA\Schema(type="string",enum={"tv","movie","ova","special","ona","music"})
     *     ),
     *
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *     @OA\Parameter(ref="#/components/parameters/limit"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns upcoming season's anime",
     *          @OA\JsonContent(
     *               ref="#/components/schemas/anime_search"
     *          )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     * @throws Exception
     */
    public function later(QueryUpcomingAnimeSeasonCommand $command)
    {
        return $this->mediator->send($command);
    }
}
