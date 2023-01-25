<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\QueryPopularEpisodesCommand;
use App\Dto\QueryPopularPromoVideosCommand;
use App\Dto\QueryRecentlyAddedEpisodesCommand;
use App\Dto\QueryRecentlyAddedPromoVideosCommand;

class WatchController extends Controller
{

    /**
     *  @OA\Get(
     *     path="/watch/episodes",
     *     operationId="getWatchRecentEpisodes",
     *     tags={"watch"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns Recently Added Episodes",
     *          @OA\JsonContent(
     *               ref="#/components/schemas/watch_episodes"
     *          )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     *
     *  @OA\Schema(
     *      schema="watch_episodes",
     *      description="Watch Episodes",
     *
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/pagination"),
     *          @OA\Schema(
     *              @OA\Property(
     *                   property="data",
     *                   type="array",
     *
     *                   @OA\Items(
     *                      type="object",
     *
     *                       @OA\Property(
     *                           property="entry",
     *                           type="object",
     *                           ref="#/components/schemas/anime_meta"
     *                       ),
     *                      @OA\Property(
     *                          property="episodes",
     *                          type="array",
     *                          description="Recent Episodes (max 2 listed)",
     *                          @OA\Items(
     *                              type="object",
     *                              @OA\Property(
     *                                  property="mal_id",
     *                                  type="string",
     *                                  description="MyAnimeList ID",
     *                              ),
     *                              @OA\Property(
     *                                  property="url",
     *                                  type="string",
     *                                  description="MyAnimeList URL",
     *                              ),
     *                              @OA\Property(
     *                                  property="title",
     *                                  type="string",
     *                                  description="Episode Title",
     *                              ),
     *                              @OA\Property(
     *                                  property="premium",
     *                                  type="boolean",
     *                                  description="For MyAnimeList Premium Users",
     *                              ),
     *                          ),
     *                      ),
     *                      @OA\Property(
     *                          property="region_locked",
     *                          type="boolean",
     *                          description="Region Locked Episode"
     *                      ),
     *                  ),
     *              ),
     *          ),
     *     },
     *  ),
     */
    public function recentEpisodes(QueryRecentlyAddedEpisodesCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/watch/episodes/popular",
     *     operationId="getWatchPopularEpisodes",
     *     tags={"watch"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns Popular Episodes",
     *          @OA\JsonContent(
     *               ref="#/components/schemas/watch_episodes"
     *          )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function popularEpisodes(QueryPopularEpisodesCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/watch/promos",
     *     operationId="getWatchRecentPromos",
     *     tags={"watch"},
     *
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns Recently Added Promotional Videos",
     *          @OA\JsonContent(
     *               ref="#/components/schemas/watch_promos"
     *          )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     *
     *  @OA\Schema(
     *      schema="watch_promos",
     *      description="Watch Promos",
     *
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/pagination"),
     *          @OA\Schema(
     *
     *              allOf={
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="title",
     *                          type="string",
     *                          description="Promo Title"
     *                      ),
     *                  ),
     *                  @OA\Schema (
     *                      @OA\Property(
     *                           property="data",
     *                           type="array",
     *
     *                           @OA\Items(
     *                              type="object",
     *                               @OA\Property(
     *                                   property="entry",
     *                                   type="object",
     *                                   ref="#/components/schemas/anime_meta"
     *                               ),
     *                              @OA\Property(
     *                                  property="trailer",
     *                                  type="array",
     *                                  @OA\Items(
     *                                      type="object",
     *                                      ref="#/components/schemas/trailer",
     *                                  ),
     *                              ),
     *                          ),
     *                      ),
     *                  ),
     *              },
     *          ),
     *     },
     *  ),
     */
    public function recentPromos(QueryRecentlyAddedPromoVideosCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/watch/promos/popular",
     *     operationId="getWatchPopularPromos",
     *     tags={"watch"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns Popular Promotional Videos",
     *          @OA\JsonContent(
     *               ref="#/components/schemas/watch_promos"
     *          )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     */
    public function popularPromos(QueryPopularPromoVideosCommand $command)
    {
        return $this->mediator->send($command);
    }

}
