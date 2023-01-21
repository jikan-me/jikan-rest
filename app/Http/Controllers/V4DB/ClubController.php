<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Club;
use App\Dto\ClubLookupCommand;
use App\Dto\ClubMembersLookupCommand;
use App\Dto\ClubRelationLookupCommand;
use App\Dto\ClubStaffLookupCommand;
use App\Http\HttpHelper;
use App\Http\HttpResponse;
use App\Http\Resources\V4\AnimeCharactersResource;
use App\Http\Resources\V4\ResultsResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\Request\Anime\AnimeCharactersAndStaffRequest;
use Jikan\Request\Club\ClubRequest;
use Jikan\Request\Club\UserListRequest;
use MongoDB\BSON\UTCDateTime;

class ClubController extends Controller
{

    /**
     *  @OA\Get(
     *     path="/clubs/{id}",
     *     operationId="getClubsById",
     *     tags={"clubs"},
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
     *         description="Returns Club Resource",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/club"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function main(ClubLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/clubs/{id}/members",
     *     operationId="getClubMembers",
     *     tags={"clubs"},
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
     *         description="Returns Club Members Resource",
     *         @OA\JsonContent(
     *              allOf={
     *                  @OA\Schema(ref="#/components/schemas/pagination"),
     *                  @OA\Schema(
     *                      ref="#/components/schemas/club_member"
     *                  )
     *              }
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * ),
     *
     * @OA\Schema(
     *      schema="club_member",
     *      description="Club Member",
     *      @OA\Property(
     *          property="data",
     *          type="array",
     *           @OA\Items(
     *               type="object",
     *               ref="#/components/schemas/user_images"
     *           ),
     *      ),
     * ),
     */
    public function members(ClubMembersLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/clubs/{id}/staff",
     *     operationId="getClubStaff",
     *     tags={"clubs"},
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
     *         description="Returns Club Staff",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/club_staff"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function staff(ClubStaffLookupCommand $command)
    {
        return $this->mediator->send($command);
    }

    /**
     *  @OA\Get(
     *     path="/clubs/{id}/relations",
     *     operationId="getClubRelations",
     *     tags={"clubs"},
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
     *         description="Returns Club Relations",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/club_relations"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function relations(ClubRelationLookupCommand $command)
    {
        return $this->mediator->send($command);
    }
}
