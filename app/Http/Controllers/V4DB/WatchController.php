<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\QueryPopularEpisodesCommand;
use App\Dto\QueryPopularPromoVideosCommand;
use App\Dto\QueryRecentlyAddedEpisodesCommand;
use App\Dto\QueryRecentlyAddedPromoVideosCommand;
use OpenApi\Attributes as OA;

class WatchController extends Controller
{
    #[OA\Get(
        path: '/watch/episodes',
        operationId: 'getWatchRecentEpisodes',
        summary: 'Recently added episodes',
        description: 'Returns recently added episodes currently trending on MyAnimeList.',
        tags: ['watch'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns Recently Added Episodes',
                content: new OA\JsonContent(ref: '#/components/schemas/watch_episodes'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    
    #[OA\Schema(schema: 'watch_episodes', description: 'Watch Episodes', allOf: [new OA\Schema(ref: '#/components/schemas/pagination'), new OA\Schema(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object', properties: [new OA\Property(property: 'entry', type: 'object', ref: '#/components/schemas/anime_meta'), new OA\Property(property: 'episodes', type: 'array', description: 'Recent Episodes (max 2 listed)', items: new OA\Items(type: 'object', properties: [new OA\Property(property: 'mal_id', type: 'string', description: 'MyAnimeList ID'), new OA\Property(property: 'url', type: 'string', description: 'MyAnimeList URL'), new OA\Property(property: 'title', type: 'string', description: 'Episode Title'), new OA\Property(property: 'premium', type: 'boolean', description: 'For MyAnimeList Premium Users')])), new OA\Property(property: 'region_locked', type: 'boolean', description: 'Region Locked Episode')]))])])]
    public function recentEpisodes(QueryRecentlyAddedEpisodesCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/watch/episodes/popular',
        operationId: 'getWatchPopularEpisodes',
        summary: 'Popular episodes',
        description: 'Returns popular episodes currently trending on MyAnimeList.',
        tags: ['watch'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns Popular Episodes',
                content: new OA\JsonContent(ref: '#/components/schemas/watch_episodes'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function popularEpisodes(QueryPopularEpisodesCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/watch/promos',
        operationId: 'getWatchRecentPromos',
        summary: 'Recently added promotional videos',
        description: 'Returns recently added promotional videos currently trending on MyAnimeList.',
        tags: ['watch'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns Recently Added Promotional Videos',
                content: new OA\JsonContent(ref: '#/components/schemas/watch_promos'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    
    #[OA\Schema(schema: 'watch_promos', description: 'Watch Promos', allOf: [new OA\Schema(ref: '#/components/schemas/pagination'), new OA\Schema(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object', properties: [new OA\Property(property: 'title', type: 'string', description: 'Promo Title'), new OA\Property(property: 'entry', ref: '#/components/schemas/anime_meta'), new OA\Property(property: 'trailer', ref: '#/components/schemas/trailer')]))])])]
    public function recentPromos(QueryRecentlyAddedPromoVideosCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/watch/promos/popular',
        operationId: 'getWatchPopularPromos',
        summary: 'Popular promotional videos',
        description: 'Returns popular promotional videos currently trending on MyAnimeList.',
        tags: ['watch'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns Popular Promotional Videos',
                content: new OA\JsonContent(ref: '#/components/schemas/watch_promos'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function popularPromos(QueryPopularPromoVideosCommand $command): mixed
    {
        return $this->mediator->send($command);
    }
}
