<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Club;
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
     *              ref="#/components/schemas/club"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function main(Request $request, int $id)
    {
        $results = Club::query()
            ->where('mal_id', $id)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $response = Club::scrape($id);

            if (HttpHelper::hasError($response)) {
                return HttpResponse::notFound($request);
            }

            if ($results->isEmpty()) {
                $meta = [
                    'createdAt' => new UTCDateTime(),
                    'modifiedAt' => new UTCDateTime(),
                    'request_hash' => $this->fingerprint
                ];
            }
            $meta['modifiedAt'] = new UTCDateTime();

            $response = $meta + $response;

            if ($results->isEmpty()) {
                Club::query()
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                Club::query()
                    ->where('mal_id', $id)
                    ->update($response);
            }

            $results = Club::query()
                ->where('mal_id', $id)
                ->get();
        }


        if ($results->isEmpty()) {
            return HttpResponse::notFound($request);
        }

        $response = (new \App\Http\Resources\V4\ClubResource(
            $results->first()
        ))->response();

        return $this->prepareResponse(
            $response,
            $results,
            $request
        );
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
     *                      ref="#/components/schemas/club member"
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
     *      schema="club member",
     *      description="Club Member",
     *      @OA\Property(
     *          property="data",
     *          type="array",
     *           @OA\Items(
     *               type="object",
     *               @OA\Property(
     *                   property="username",
     *                   type="string",
     *                   description="MyAnimeList Username"
     *               ),
     *               @OA\Property(
     *                   property="url",
     *                   type="string",
     *                   description="MyAnimeList URL"
     *               ),
     *               @OA\Property(
     *                   property="image_url",
     *                   type="string",
     *                   description="MyAnimeList Image URL"
     *               ),
     *           ),
     *      ),
     * ),
     */
    public function members(Request $request, int $id)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $page = $request->get('page') ?? 1;
            $anime = ['results' => $this->jikan->getClubUsers(new UserListRequest($id, $page))];
            $response = \json_decode($this->serializer->serialize($anime, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new ResultsResource(
            $results->first()
        ))->response();

        return $this->prepareResponse(
            $response,
            $results,
            $request
        );
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
     *              ref="#/components/schemas/club staff"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function staff(Request $request, int $id)
    {
        $results = Club::query()
            ->where('mal_id', $id)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $response = Club::scrape($id);

            if (HttpHelper::hasError($response)) {
                return HttpResponse::notFound($request);
            }

            if ($results->isEmpty()) {
                $meta = [
                    'createdAt' => new UTCDateTime(),
                    'modifiedAt' => new UTCDateTime(),
                    'request_hash' => $this->fingerprint
                ];
            }
            $meta['modifiedAt'] = new UTCDateTime();

            $response = $meta + $response;

            if ($results->isEmpty()) {
                Club::query()
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                Club::query()
                    ->where('mal_id', $id)
                    ->update($response);
            }

            $results = Club::query()
                ->where('mal_id', $id)
                ->get();
        }


        if ($results->isEmpty()) {
            return HttpResponse::notFound($request);
        }

        $response = (new \App\Http\Resources\V4\ClubStaffResource(
            $results->first()
        ))->response();

        return $this->prepareResponse(
            $response,
            $results,
            $request
        );
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
     *              ref="#/components/schemas/club relations"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function relations(Request $request, int $id)
    {
        $results = Club::query()
            ->where('mal_id', $id)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $response = Club::scrape($id);

            if (HttpHelper::hasError($response)) {
                return HttpResponse::notFound($request);
            }

            if ($results->isEmpty()) {
                $meta = [
                    'createdAt' => new UTCDateTime(),
                    'modifiedAt' => new UTCDateTime(),
                    'request_hash' => $this->fingerprint
                ];
            }
            $meta['modifiedAt'] = new UTCDateTime();

            $response = $meta + $response;

            if ($results->isEmpty()) {
                Club::query()
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                Club::query()
                    ->where('mal_id', $id)
                    ->update($response);
            }

            $results = Club::query()
                ->where('mal_id', $id)
                ->get();
        }


        if ($results->isEmpty()) {
            return HttpResponse::notFound($request);
        }

        $response = (new \App\Http\Resources\V4\ClubRelationsResource(
            $results->first()
        ))->response();

        return $this->prepareResponse(
            $response,
            $results,
            $request
        );
    }
}
