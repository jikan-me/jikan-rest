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
use OpenApi\Attributes as OA;

class AnimeController extends Controller
{
    #[OA\Get(
        path: '/anime/{id}/full',
        operationId: 'getAnimeFullById',
        summary: 'Complete anime resource',
        description: 'Returns the complete anime resource, including primary data plus related sub-resources, for an anime by **id**.',
        tags: ['anime'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns complete anime resource data',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/anime_full'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function full(AnimeFullLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/anime/{id}',
        operationId: 'getAnimeById',
        summary: 'Anime details',
        description: 'Returns the details of an anime by **id**.',
        tags: ['anime'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns anime resource',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/anime'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function main(AnimeLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/anime/{id}/characters',
        operationId: 'getAnimeCharacters',
        summary: 'Anime characters',
        description: 'Returns the characters of an anime by **id**, together with their voice actors.',
        tags: ['anime'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns anime characters resource',
                content: new OA\JsonContent(ref: '#/components/schemas/anime_characters'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function characters(AnimeCharactersLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/anime/{id}/staff',
        operationId: 'getAnimeStaff',
        summary: 'Anime staff',
        description: 'Returns the staff of an anime by **id** along with their positions.',
        tags: ['anime'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns anime staff resource',
                content: new OA\JsonContent(ref: '#/components/schemas/anime_staff'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function staff(AnimeStaffLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/anime/{id}/episodes',
        operationId: 'getAnimeEpisodes',
        summary: 'Anime episodes',
        description: 'Returns the list of episodes for an anime by **id**, paginated by **page**.',
        tags: ['anime'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(ref: '#/components/parameters/page'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns a list of anime episodes',
                content: new OA\JsonContent(ref: '#/components/schemas/anime_episodes'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]

    #[OA\Schema(schema: 'anime_episodes', description: 'Anime Episodes Resource', allOf: [new OA\Schema(ref: '#/components/schemas/pagination'), new OA\Schema(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object', properties: [new OA\Property(property: 'mal_id', type: 'integer', description: 'MyAnimeList ID'), new OA\Property(property: 'url', type: 'string', description: 'MyAnimeList URL. This is the URL of the episode\'s video. If there is no video url, this will be null.', nullable: true), new OA\Property(property: 'title', type: 'string', description: 'Title'), new OA\Property(property: 'title_japanese', type: 'string', description: 'Title Japanese', nullable: true), new OA\Property(property: 'title_romanji', type: 'string', description: 'title_romanji', nullable: true), new OA\Property(property: 'aired', type: 'string', description: 'Aired Date ISO8601', nullable: true), new OA\Property(property: 'score', type: 'float', description: 'Aggregated episode score (1.00 - 5.00) based on MyAnimeList user voting', minimum: 1, maximum: 5, nullable: true), new OA\Property(property: 'filler', type: 'boolean', description: 'Filler episode'), new OA\Property(property: 'recap', type: 'boolean', description: 'Recap episode'), new OA\Property(property: 'forum_url', type: 'string', description: 'Episode discussion forum URL', nullable: true)]))])])]
    public function episodes(AnimeEpisodesLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/anime/{id}/episodes/{episode}',
        operationId: 'getAnimeEpisodeById',
        summary: 'Anime episode',
        description: 'Returns a single episode of an anime by **id** and **episode**.',
        tags: ['anime'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(
                name: 'episode',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns a single anime episode resource',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/anime_episode'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function episode(AnimeEpisodeLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/anime/{id}/news',
        operationId: 'getAnimeNews',
        summary: 'Anime news',
        description: 'Returns recent news articles for an anime by **id**, paginated by **page**.',
        tags: ['anime'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(ref: '#/components/parameters/page'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns a list of news articles related to the entry',
                content: new OA\JsonContent(ref: '#/components/schemas/anime_news'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]

    #[OA\Schema(schema: 'anime_news', description: 'Anime News Resource', allOf: [new OA\Schema(ref: '#/components/schemas/pagination'), new OA\Schema(ref: '#/components/schemas/news')])]
    public function news(AnimeNewsLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/anime/{id}/forum',
        operationId: 'getAnimeForum',
        summary: 'Anime forum topics',
        description: 'Returns the forum topics of an anime by **id**. Use **filter** to restrict to all, episode, or other topics.',
        tags: ['anime'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(
                name: 'filter',
                in: 'query',
                required: false,
                description: 'Filter topics',
                schema: new OA\Schema(type: 'string', enum: ['all', 'episode', 'other']),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns a list of forum topics related to the entry',
                content: new OA\JsonContent(ref: '#/components/schemas/forum'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function forum(AnimeForumLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/anime/{id}/videos',
        operationId: 'getAnimeVideos',
        summary: 'Anime videos',
        description: 'Returns the promotional videos, episodes, and music videos of an anime by **id**.',
        tags: ['anime'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns videos related to the entry',
                content: new OA\JsonContent(ref: '#/components/schemas/anime_videos'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function videos(AnimeVideosLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/anime/{id}/videos/episodes',
        operationId: 'getAnimeVideosEpisodes',
        summary: 'Anime video episodes',
        description: 'Returns the episode videos of an anime by **id**, paginated by **page**.',
        tags: ['anime'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(ref: '#/components/parameters/page'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns episode videos related to the entry',
                content: new OA\JsonContent(ref: '#/components/schemas/anime_videos_episodes'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]

    #[OA\Schema(schema: 'anime_videos_episodes', description: 'Anime Videos Episodes Resource', allOf: [new OA\Schema(ref: '#/components/schemas/pagination'), new OA\Schema(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object', properties: [new OA\Property(property: 'mal_id', type: 'integer', description: 'MyAnimeList ID or Episode Number'), new OA\Property(property: 'title', type: 'string', description: 'Episode Title'), new OA\Property(property: 'episode', type: 'string', description: 'Episode Subtitle'), new OA\Property(property: 'url', type: 'string', description: 'Episode Page URL'), new OA\Property(property: 'images', ref: '#/components/schemas/common_images')]))])])]
    public function videosEpisodes(AnimeVideosEpisodesLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/anime/{id}/pictures',
        operationId: 'getAnimePictures',
        summary: 'Anime pictures',
        description: 'Returns the pictures of an anime by **id**.',
        tags: ['anime'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns pictures related to the entry',
                content: new OA\JsonContent(ref: '#/components/schemas/pictures_variants'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function pictures(AnimePicturesLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/anime/{id}/statistics',
        operationId: 'getAnimeStatistics',
        summary: 'Anime statistics',
        description: 'Returns the statistics of an anime by **id**, such as watching, completed, and on-hold counts.',
        tags: ['anime'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns anime statistics',
                content: new OA\JsonContent(ref: '#/components/schemas/anime_statistics'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function stats(AnimeStatsLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/anime/{id}/moreinfo',
        operationId: 'getAnimeMoreInfo',
        summary: 'Anime more info',
        description: 'Returns additional trivia and background information about an anime by **id**.',
        tags: ['anime'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns anime statistics',
                content: new OA\JsonContent(ref: '#/components/schemas/moreinfo'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function moreInfo(AnimeMoreInfoLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/anime/{id}/recommendations',
        operationId: 'getAnimeRecommendations',
        summary: 'Anime recommendations',
        description: 'Returns the recommendations for an anime by **id**.',
        tags: ['anime'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns anime recommendations',
                content: new OA\JsonContent(ref: '#/components/schemas/entry_recommendations'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function recommendations(AnimeRecommendationsLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/anime/{id}/userupdates',
        operationId: 'getAnimeUserUpdates',
        summary: 'Anime user updates',
        description: 'Returns the recent list updates of users for an anime by **id**, paginated by **page**.',
        tags: ['anime'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(ref: '#/components/parameters/page'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns a list of users who have added/updated/removed the entry on their list',
                content: new OA\JsonContent(ref: '#/components/schemas/anime_userupdates'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function userupdates(AnimeUserUpdatesLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/anime/{id}/reviews',
        operationId: 'getAnimeReviews',
        summary: 'Anime reviews',
        description: 'Returns the reviews of an anime by **id**. Supports pagination via **page**, with **preliminary** and **spoilers** toggles for review types.',
        tags: ['anime'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/preliminary'),
            new OA\Parameter(ref: '#/components/parameters/spoilers'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns anime reviews',
                content: new OA\JsonContent(ref: '#/components/schemas/anime_reviews'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function reviews(AnimeReviewsLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    /**
     * @OA\Response(
     * response="400",
     * description="Error: Bad request. When required parameters were not supplied.",
     * ),
     * )
     */
    #[OA\Get(
        path: '/anime/{id}/relations',
        operationId: 'getAnimeRelations',
        summary: 'Anime relations',
        description: 'Returns the related entries for an anime by **id**.',
        tags: ['anime'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns anime relations',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/relation'),
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function relations(AnimeRelationsLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/anime/{id}/themes',
        operationId: 'getAnimeThemes',
        summary: 'Anime themes',
        description: 'Returns the opening and ending themes of an anime by **id**.',
        tags: ['anime'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns anime themes',
                content: new OA\JsonContent(ref: '#/components/schemas/anime_themes'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function themes(AnimeThemesLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/anime/{id}/external',
        operationId: 'getAnimeExternal',
        summary: 'Anime external links',
        description: 'Returns the external links of an anime by **id**.',
        tags: ['anime'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns anime external links',
                content: new OA\JsonContent(ref: '#/components/schemas/external_links'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function external(AnimeExternalLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/anime/{id}/streaming',
        operationId: 'getAnimeStreaming',
        summary: 'Anime streaming links',
        description: 'Returns the streaming links of an anime by **id**.',
        tags: ['anime'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns anime streaming links',
                content: new OA\JsonContent(ref: '#/components/schemas/external_links'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function streaming(AnimeStreamingLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }
}
