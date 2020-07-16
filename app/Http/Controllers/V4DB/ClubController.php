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
     *     tags={"club"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns Club Resource",
     *         @OA\JsonContent()
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
                    ->where('request_hash', $this->fingerprint)
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
     *     tags={"club"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns Club Members Resource",
     *         @OA\JsonContent()
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
                DB::table($this->getRouteTable($request))
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                DB::table($this->getRouteTable($request))
                    ->where('request_hash', $this->fingerprint)
                    ->update($response);
            }

            $results = DB::table($this->getRouteTable($request))
                ->where('request_hash', $this->fingerprint)
                ->get();
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
}
