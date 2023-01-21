<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\AnimeCharactersLookupCommand;
use App\Dto\AnimeEpisodeLookupCommand;
use App\Dto\AnimeEpisodesLookupCommand;
use App\Dto\AnimeExternalLookupCommand;
use App\Dto\AnimeForumLookupCommand;
use App\Dto\AnimeFullLookupCommand;
use App\Dto\AnimeLookupCommand;
use App\Dto\AnimeMoreInfoLookupCommand;
use App\Dto\AnimeNewsLookupCommand;
use App\Dto\AnimePicturesLookupCommand;
use App\Dto\AnimeRecommendationsLookupCommand;
use App\Dto\AnimeRelationsLookupCommand;
use App\Dto\AnimeReviewsLookupCommand;
use App\Dto\AnimeStaffLookupCommand;
use App\Dto\AnimeStatsLookupCommand;
use App\Dto\AnimeStreamingLookupCommand;
use App\Dto\AnimeThemesLookupCommand;
use App\Dto\AnimeUserUpdatesLookupCommand;
use App\Dto\AnimeVideosEpisodesLookupCommand;
use App\Dto\AnimeVideosLookupCommand;
use Illuminate\Http\Request;

class AnimeController extends Controller
{
    /**
     *  @OA\Get(
     *     path="/anime/{id}/full",
     *     operationId="getAnimeFullById",
     *     tags={"anime"},
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
     *         description="Returns complete anime resource data",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/anime_full"
     *              )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function full(AnimeFullLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/anime/{id}",
     *     operationId="getAnimeById",
     *     tags={"anime"},
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
     *         description="Returns anime resource",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/anime"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function main(AnimeLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/anime/{id}/characters",
     *     operationId="getAnimeCharacters",
     *     tags={"anime"},
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
     *         description="Returns anime characters resource",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/anime_characters"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function characters(AnimeCharactersLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/anime/{id}/staff",
     *     operationId="getAnimeStaff",
     *     tags={"anime"},
     *
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Returns anime staff resource",
     *          @OA\JsonContent(
     *               ref="#/components/schemas/anime_staff"
     *          )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function staff(AnimeStaffLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/anime/{id}/episodes",
     *     operationId="getAnimeEpisodes",
     *     tags={"anime"},
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
     *         description="Returns a list of anime episodes",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/anime_episodes"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     *
     *  @OA\Schema(
     *      schema="anime_episodes",
     *      description="Anime Episodes Resource",
     *
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/pagination"),
     *          @OA\Schema(
     *          @OA\Property(
     *               property="data",
     *               type="array",
     *               @OA\Items(
     *                   type="object",
     *                   @OA\Property(
     *                       property="mal_id",
     *                       type="integer",
     *                       description="MyAnimeList ID"
     *                   ),
     *                   @OA\Property(
     *                       property="url",
     *                       type="string",
     *                       description="MyAnimeList URL"
     *                   ),
     *                   @OA\Property(
     *                       property="title",
     *                       type="string",
     *                       description="Title"
     *                   ),
     *                   @OA\Property(
     *                       property="title_japanese",
     *                       type="string",
     *                       description="Title Japanese",
     *                       nullable=true
     *                   ),
     *                   @OA\Property(
     *                       property="title_romanji",
     *                       type="string",
     *                       description="title_romanji",
     *                       nullable=true
     *                   ),
     *                   @OA\Property(
     *                       property="duration",
     *                       type="integer",
     *                       description="Episode duration in seconds",
     *                       nullable=true
     *                   ),
     *                   @OA\Property(
     *                       property="aired",
     *                       type="string",
     *                       description="Aired Date ISO8601",
     *                       nullable=true
     *                   ),
     *                   @OA\Property(
     *                       property="filler",
     *                       type="boolean",
     *                       description="Filler episode"
     *                   ),
     *                   @OA\Property(
     *                       property="recap",
     *                       type="boolean",
     *                       description="Recap episode"
     *                   ),
     *                   @OA\Property(
     *                       property="forum_url",
     *                       type="string",
     *                       description="Episode discussion forum URL",
     *                       nullable=true
     *                   ),
     *               ),
     *          ),
     *          ),
     *      }
     *  )
     */
    public function episodes(AnimeEpisodesLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/anime/{id}/episodes/{episode}",
     *     operationId="getAnimeEpisodeById",
     *     tags={"anime"},
     *
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *       name="episode",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns a single anime episode resource",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/anime_episode"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function episode(AnimeEpisodeLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/anime/{id}/news",
     *     operationId="getAnimeNews",
     *     tags={"anime"},
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
     *         description="Returns a list of news articles related to the entry",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/anime_news"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     *
     *  @OA\Schema(
     *      schema="anime_news",
     *      description="Anime News Resource",
     *
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/pagination"),
     *          @OA\Schema(
     *              ref="#/components/schemas/news",
     *          ),
     *      }
     *  )
     */
    public function news(AnimeNewsLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/anime/{id}/forum",
     *     operationId="getAnimeForum",
     *     tags={"anime"},
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
     *         description="Returns a list of forum topics related to the entry",
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
    public function forum(AnimeForumLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/anime/{id}/videos",
     *     operationId="getAnimeVideos",
     *     tags={"anime"},
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
     *         description="Returns videos related to the entry",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/anime_videos"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function videos(AnimeVideosLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/anime/{id}/videos/episodes",
     *     operationId="getAnimeVideosEpisodes",
     *     tags={"anime"},
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
     *         description="Returns episode videos related to the entry",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/anime_videos_episodes"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     *  ),
     *
     *
     *  @OA\Schema(
     *      schema="anime_videos_episodes",
     *      description="Anime Videos Episodes Resource",
     *
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/pagination"),
     *          @OA\Schema(
     *               @OA\Property(
     *                    property="data",
     *                    type="array",
     *                    @OA\Items(
     *                        type="object",
     *                        @OA\Property(
     *                            property="mal_id",
     *                            type="integer",
     *                            description="MyAnimeList ID or Episode Number"
     *                        ),
     *                        @OA\Property(
     *                            property="title",
     *                            type="string",
     *                            description="Episode Title"
     *                        ),
     *                        @OA\Property(
     *                            property="episode",
     *                            type="string",
     *                            description="Episode Subtitle"
     *                        ),
     *                        @OA\Property(
     *                            property="url",
     *                            type="string",
     *                            description="Episode Page URL",
     *                        ),
     *                        @OA\Property(
     *                            property="images",
     *                            ref="#/components/schemas/common_images"
     *                        ),
     *                    ),
     *               ),
     *          ),
     *      }
     *  )
     */
    public function videosEpisodes(AnimeVideosEpisodesLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/anime/{id}/pictures",
     *     operationId="getAnimePictures",
     *     tags={"anime"},
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
     *              ref="#/components/schemas/pictures_variants"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     *
     */
    public function pictures(AnimePicturesLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/anime/{id}/statistics",
     *     operationId="getAnimeStatistics",
     *     tags={"anime"},
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
     *              ref="#/components/schemas/anime_statistics"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function stats(AnimeStatsLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/anime/{id}/moreinfo",
     *     operationId="getAnimeMoreInfo",
     *     tags={"anime"},
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
     *              ref="#/components/schemas/moreinfo"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function moreInfo(AnimeMoreInfoLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/anime/{id}/recommendations",
     *     operationId="getAnimeRecommendations",
     *     tags={"anime"},
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
     *         description="Returns anime recommendations",
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
    public function recommendations(AnimeRecommendationsLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/anime/{id}/userupdates",
     *     operationId="getAnimeUserUpdates",
     *     tags={"anime"},
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
     *         description="Returns a list of users who have added/updated/removed the entry on their list",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/anime_userupdates"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function userupdates(AnimeUserUpdatesLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/anime/{id}/reviews",
     *     operationId="getAnimeReviews",
     *     tags={"anime"},
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
     *         description="Returns anime reviews",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/anime_reviews"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function reviews(AnimeReviewsLookupCommand $command)
    {
        return $this->mediator->send($command);
    }


    /**
     *  @OA\Get(
     *     path="/anime/{id}/relations",
     *     operationId="getAnimeRelations",
     *     tags={"anime"},
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
     *         description="Returns anime relations",
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
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function relations(AnimeRelationsLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/anime/{id}/themes",
     *     operationId="getAnimeThemes",
     *     tags={"anime"},
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
     *         description="Returns anime themes",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/anime_themes"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function themes(AnimeThemesLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/anime/{id}/external",
     *     operationId="getAnimeExternal",
     *     tags={"anime"},
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
     *         description="Returns anime external links",
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
    public function external(AnimeExternalLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/anime/{id}/streaming",
     *     operationId="getAnimeStreaming",
     *     tags={"anime"},
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
     *         description="Returns anime streaming links",
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
    public function streaming(AnimeStreamingLookupCommand $command)
    {
        return $this->mediator->send($command);
    }
}
