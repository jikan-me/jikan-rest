<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Character;
use App\Http\HttpHelper;
use App\Http\HttpResponse;
use App\Http\Resources\V4\CharacterAnimeCollection;
use App\Http\Resources\V4\CharacterMangaCollection;
use App\Http\Resources\V4\CharacterMangaResource;
use App\Http\Resources\V4\CharacterSeiyuuCollection;
use App\Http\Resources\V4\PersonMangaCollection;
use App\Http\Resources\V4\PicturesResource;
use App\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\Request\Anime\AnimePicturesRequest;
use Jikan\Request\Character\CharacterRequest;
use Jikan\Request\Character\CharacterPicturesRequest;
use MongoDB\BSON\UTCDateTime;

class CharacterController extends Controller
{

    /**
     *  @OA\Get(
     *     path="/characters/{id}",
     *     operationId="getCharacterById",
     *     tags={"characters"},
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
     *         description="Returns character resource",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/character"
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
        $results = Character::query()
            ->where('mal_id', $id)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $response = Character::scrape($id);

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
                Character::query()
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                Character::query()
                    ->where('mal_id', $id)
                    ->update($response);
            }

            $results = Character::query()
                ->where('mal_id', $id)
                ->get();
        }

        if ($results->isEmpty()) {
            return HttpResponse::notFound($request);
        }

        $response = (new \App\Http\Resources\V4\CharacterResource(
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
     *     path="/characters/{id}/anime",
     *     operationId="getCharacterAnime",
     *     tags={"characters"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns anime that character is in",
     *         @OA\JsonContent(ref="#/components/schemas/character anime")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function anime(Request $request, int $id)
    {
        $results = Character::query()
            ->where('mal_id', $id)
            ->get();


        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $response = Character::scrape($id);

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
                Character::query()
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                Character::query()
                    ->where('mal_id', $id)
                    ->update($response);
            }

            $results = Character::query()
                ->where('mal_id', $id)
                ->get();
        }

        if ($results->isEmpty()) {
            return HttpResponse::notFound($request);
        }

        $response = (new CharacterAnimeCollection(
            $results->first()['animeography']
        ))->response();

        return $this->prepareResponse(
            $response,
            $results,
            $request
        );
    }


    /**
     *  @OA\Get(
     *     path="/characters/{id}/manga",
     *     operationId="getCharacterManga",
     *     tags={"characters"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns manga that character is in",
     *         @OA\JsonContent(ref="#/components/schemas/character manga")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function manga(Request $request, int $id)
    {
        $results = Character::query()
            ->where('mal_id', $id)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $response = Character::scrape($id);

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
                Character::query()
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                Character::query()
                    ->where('mal_id', $id)
                    ->update($response);
            }

            $results = Character::query()
                ->where('mal_id', $id)
                ->get();
        }

        if ($results->isEmpty()) {
            return HttpResponse::notFound($request);
        }

        $response = (new CharacterMangaCollection(
            $results->first()['mangaography']
        ))->response();

        return $this->prepareResponse(
            $response,
            $results,
            $request
        );
    }

    /**
     *  @OA\Get(
     *     path="/characters/{id}/voices",
     *     operationId="getCharacterVoiceActors",
     *     tags={"characters"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns the character's voice actors",
     *         @OA\JsonContent(ref="#/components/schemas/character voice actors")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function voices(Request $request, int $id)
    {
        $results = Character::query()
            ->where('mal_id', $id)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $response = Character::scrape($id);

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
                Character::query()
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                Character::query()
                    ->where('mal_id', $id)
                    ->update($response);
            }

            $results = Character::query()
                ->where('mal_id', $id)
                ->get();
        }

        if ($results->isEmpty()) {
            return HttpResponse::notFound($request);
        }

        $response = (new CharacterSeiyuuCollection(
            $results->first()['voice_actors']
        ))->response();

        return $this->prepareResponse(
            $response,
            $results,
            $request
        );
    }

    /**
     *  @OA\Get(
     *     path="/characters/{id}/pictures",
     *     operationId="getCharacterPictures",
     *     tags={"characters"},
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
     *              ref="#/components/schemas/pictures"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     * 
     *  @OA\Schema(
     *      schema="character pictures",
     *      description="Character Pictures",
     *      @OA\Property(
     *          property="data",
     *          type="array",
     * 
     *          @OA\Items(
     *              @OA\Property(
     *                  property="image_url",
     *                  type="string",
     *                  description="Default JPG Image Size URL"
     *              ),
     *              @OA\Property(
     *                  property="large_image_url",
     *                  type="string",
     *                  description="Large JPG Image Size URL"
     *              ),
     *          )
     *      )
     *  )
     */
    public function pictures(Request $request, int $id)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $character = ['pictures' => $this->jikan->getCharacterPictures(new CharacterPicturesRequest($id))];
            $response = \json_decode($this->serializer->serialize($character, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new PicturesResource(
            $results->first()
        ))->response();

        return $this->prepareResponse(
            $response,
            $results,
            $request
        );
    }
}
