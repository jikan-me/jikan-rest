<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\QueryAnimeRecommendationsCommand;
use App\Dto\QueryMangaRecommendationsCommand;
use OpenApi\Attributes as OA;

class RecommendationsController extends Controller
{
    #[OA\Get(
        path: '/recommendations/anime',
        operationId: 'getRecentAnimeRecommendations',
        summary: 'Recent anime recommendations',
        description: 'Retrieves the most recent recent anime recommendations from MyAnimeList.',
        tags: ['recommendations'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns recent anime recommendations',
                content: new OA\JsonContent(ref: '#/components/schemas/recommendations'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function anime(QueryAnimeRecommendationsCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/recommendations/manga',
        operationId: 'getRecentMangaRecommendations',
        summary: 'Recent manga recommendations',
        description: 'Retrieves the most recent recent manga recommendations from MyAnimeList.',
        tags: ['recommendations'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns recent manga recommendations',
                content: new OA\JsonContent(ref: '#/components/schemas/recommendations'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function manga(QueryMangaRecommendationsCommand $command): mixed
    {
        return $this->mediator->send($command);
    }
}
