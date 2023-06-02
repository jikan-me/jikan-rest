<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\MangaCharactersLookupCommand;
use App\Dto\MangaExternalLookupCommand;
use App\Dto\MangaForumLookupCommand;
use App\Dto\MangaFullLookupCommand;
use App\Dto\MangaLookupCommand;
use App\Dto\MangaMoreInfoLookupCommand;
use App\Dto\MangaNewsLookupCommand;
use App\Dto\MangaPicturesLookupCommand;
use App\Dto\MangaRecommendationsLookupCommand;
use App\Dto\MangaRelationsLookupCommand;
use App\Dto\MangaReviewsLookupCommand;
use App\Dto\MangaStatsLookupCommand;
use App\Dto\MangaUserUpdatesLookupCommand;
use Illuminate\Http\Request;

class MangaController extends Controller
{
    /**
     *  @OA\Get(
     *     path="/manga/{id}/full",
     *     operationId="getMangaFullById",
     *     tags={"manga"},
     *
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns complete manga resource data",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/manga_full"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function full(MangaFullLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/manga/{id}",
     *     operationId="getMangaById",
     *     tags={"manga"},
     *
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns pictures related to the entry",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/manga"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function main(MangaLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/manga/{id}/characters",
     *     operationId="getMangaCharacters",
     *     tags={"manga"},
     *
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns manga characters resource",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/manga_characters"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function characters(MangaCharactersLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/manga/{id}/news",
     *     operationId="getMangaNews",
     *     tags={"manga"},
     *
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of manga news topics",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/manga_news"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     *
     *  @OA\Schema(
     *      schema="manga_news",
     *      description="Manga News Resource",
     *
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/pagination"),
     *          @OA\Schema(ref="#/components/schemas/news"),
     *      }
     *  )
     */
    public function news(MangaNewsLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/manga/{id}/forum",
     *     operationId="getMangaTopics",
     *     tags={"manga"},
     *
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *      @OA\Parameter(
     *          name="filter",
     *          in="query",
     *          required=false,
     *          description="Filter topics",
     *          @OA\Schema(type="string",enum={"all", "episode", "other"})
     *      ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of manga forum topics",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/forum"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function forum(MangaForumLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/manga/{id}/pictures",
     *     operationId="getMangaPictures",
     *     tags={"manga"},
     *
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of manga pictures",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/manga_pictures"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     * @OA\Schema(
     *     schema="manga_pictures",
     *     description="Manga Pictures",
     *     @OA\Property(
     *         property="data",
     *         type="array",
     *
     *         @OA\Items(
     *              type="object",
     *              ref="#/components/schemas/manga_images"
     *         )
     *     )
     * )
     */
    public function pictures(MangaPicturesLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/manga/{id}/statistics",
     *     operationId="getMangaStatistics",
     *     tags={"manga"},
     *
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns anime statistics",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/manga_statistics"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function stats(MangaStatsLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/manga/{id}/moreinfo",
     *     operationId="getMangaMoreInfo",
     *     tags={"manga"},
     *
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns manga moreinfo",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/moreinfo"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function moreInfo(MangaMoreInfoLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/manga/{id}/recommendations",
     *     operationId="getMangaRecommendations",
     *     tags={"manga"},
     *
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns manga recommendations",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/entry_recommendations"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function recommendations(MangaRecommendationsLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/manga/{id}/userupdates",
     *     operationId="getMangaUserUpdates",
     *     tags={"manga"},
     *
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns manga user updates",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/manga_userupdates"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function userupdates(MangaUserUpdatesLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     * @OA\Get(
     *     path="/manga/{id}/reviews",
     *     operationId="getMangaReviews",
     *     tags={"manga"},
     *
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns manga reviews",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/manga_reviews"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     * @throws \Exception
     */
    public function reviews(MangaReviewsLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/manga/{id}/relations",
     *     operationId="getMangaRelations",
     *     tags={"manga"},
     *
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns manga relations",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                   property="data",
     *                   type="array",
     *
     *                   @OA\Items(
     *                          ref="#/components/schemas/relation"
     *                   ),
     *              ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function relations(MangaRelationsLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/manga/{id}/external",
     *     operationId="getMangaExternal",
     *     tags={"manga"},
     *
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns manga external links",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/external_links"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function external(MangaExternalLookupCommand $command)
    {
        return $this->mediator->send($command);
    }
}
