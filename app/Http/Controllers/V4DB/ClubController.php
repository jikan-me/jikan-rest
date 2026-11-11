<?php

namespace App\Http\Controllers\V4DB;

use App\Dto\ClubLookupCommand;
use App\Dto\ClubMembersLookupCommand;
use App\Dto\ClubRelationLookupCommand;
use App\Dto\ClubStaffLookupCommand;
use OpenApi\Attributes as OA;

class ClubController extends Controller
{
    #[OA\Get(
        path: '/clubs/{id}',
        operationId: 'getClubsById',
        summary: 'Club details',
        description: 'Returns the details of a club by **id**.',
        tags: ['clubs'],
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
                description: 'Returns Club Resource',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/club'),
                    ],
                ),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function main(ClubLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/clubs/{id}/members',
        operationId: 'getClubMembers',
        summary: 'Club members',
        description: 'Returns the members of a club by **id**, paginated by **page**.',
        tags: ['clubs'],
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
                description: 'Returns Club Members Resource',
                content: new OA\JsonContent(allOf: [new OA\Schema(ref: '#/components/schemas/pagination'), new OA\Schema(ref: '#/components/schemas/club_member')]),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]

    #[OA\Schema(
        schema: 'club_member',
        description: 'Club Member',
        properties: [
            new OA\Property(
                property: 'data',
                type: 'array',
                items: new OA\Items(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'username', type: 'string', description: 'User\'s username'),
                        new OA\Property(property: 'url', type: 'string', description: 'User URL'),
                        new OA\Property(property: 'images', type: 'object', ref: '#/components/schemas/user_images'),
                    ],
                ),
            ),
        ],
    )]
    public function members(ClubMembersLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/clubs/{id}/staff',
        operationId: 'getClubStaff',
        summary: 'Club staff',
        description: 'Returns the staff of a club by **id**.',
        tags: ['clubs'],
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
                description: 'Returns Club Staff',
                content: new OA\JsonContent(ref: '#/components/schemas/club_staff'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function staff(ClubStaffLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }

    #[OA\Get(
        path: '/clubs/{id}/relations',
        operationId: 'getClubRelations',
        summary: 'Club relations',
        description: 'Returns the anime, manga, and characters related to a club by **id**.',
        tags: ['clubs'],
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
                description: 'Returns Club Relations',
                content: new OA\JsonContent(ref: '#/components/schemas/club_relations'),
            ),
            new OA\Response(response: 400, description: 'Error: Bad request. When required parameters were not supplied.'),
        ],
    )]
    public function relations(ClubRelationLookupCommand $command): mixed
    {
        return $this->mediator->send($command);
    }
}
