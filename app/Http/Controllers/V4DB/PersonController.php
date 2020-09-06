<?php

namespace App\Http\Controllers\V4DB;

use App\Character;
use App\Http\HttpHelper;
use App\Http\HttpResponse;
use App\Http\Resources\V4\PersonAnimeCollection;
use App\Http\Resources\V4\PersonAnimeResource;
use App\Http\Resources\V4\PersonMangaCollection;
use App\Http\Resources\V4\PersonVoiceResource;
use App\Http\Resources\V4\PersonVoicesCollection;
use App\Http\Resources\V4\PicturesResource;
use App\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\Request\Character\CharacterPicturesRequest;
use Jikan\Request\Person\PersonRequest;
use Jikan\Request\Person\PersonPicturesRequest;
use MongoDB\BSON\UTCDateTime;

class PersonController extends Controller
{
    /**
     *  @OA\Get(
     *     path="/people/{id}",
     *     operationId="getPersonById",
     *     tags={"people"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns person resource",
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
        $results = Person::query()
            ->where('mal_id', $id)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $response = Person::scrape($id);

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
                Person::query()
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                Person::query()
                    ->where('request_hash', $this->fingerprint)
                    ->update($response);
            }

            $results = Person::query()
                ->where('mal_id', $id)
                ->get();
        }

        if ($results->isEmpty()) {
            return HttpResponse::notFound($request);
        }

        $response = (new \App\Http\Resources\V4\PersonResource(
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
     *     path="/people/{id}/anime",
     *     operationId="getPersonAnime",
     *     tags={"people"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns person's anime staff positions",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function anime(Request $request, int $id)
    {
        $results = Person::query()
            ->where('mal_id', $id)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $response = Person::scrape($id);

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
                Person::query()
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                Person::query()
                    ->where('mal_id', $id)
                    ->update($response);
            }

            $results = Person::query()
                ->where('mal_id', $id)
                ->get();
        }

        if ($results->isEmpty()) {
            return HttpResponse::notFound($request);
        }

        $response = (new PersonAnimeCollection(
            $results->first()['anime_staff_positions']
        ))->response();

        return $this->prepareResponse(
            $response,
            $results,
            $request
        );
    }

    /**
     *  @OA\Get(
     *     path="/people/{id}/voices",
     *     operationId="getPersonVoices",
     *     tags={"people"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns person's voice acting roles",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function voices(Request $request, int $id)
    {
        $results = Person::query()
            ->where('mal_id', $id)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $response = Person::scrape($id);

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
                Person::query()
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                Person::query()
                    ->where('mal_id', $id)
                    ->update($response);
            }

            $results = Person::query()
                ->where('mal_id', $id)
                ->get();
        }

        if ($results->isEmpty()) {
            return HttpResponse::notFound($request);
        }

        $response = (new PersonVoicesCollection(
            $results->first()['voice_acting_roles']
        ))->response();

        return $this->prepareResponse(
            $response,
            $results,
            $request
        );
    }

    /**
     *  @OA\Get(
     *     path="/people/{id}/manga",
     *     operationId="getPersonManga",
     *     tags={"people"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns person's published manga",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function manga(Request $request, int $id)
    {
        $results = Person::query()
            ->where('mal_id', $id)
            ->get();

        if (
            $results->isEmpty()
            || $this->isExpired($request, $results)
        ) {
            $response = Person::scrape($id);

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
                Person::query()
                    ->insert($response);
            }

            if ($this->isExpired($request, $results)) {
                Person::query()
                    ->where('mal_id', $id)
                    ->update($response);
            }

            $results = Person::query()
                ->where('mal_id', $id)
                ->get();
        }

        if ($results->isEmpty()) {
            return HttpResponse::notFound($request);
        }

        $response = (new PersonMangaCollection(
            $results->first()['published_manga']
        ))->response();

        return $this->prepareResponse(
            $response,
            $results,
            $request
        );
    }

    /**
     *  @OA\Get(
     *     path="/people/{id}/pictures",
     *     operationId="getPersonPictures",
     *     tags={"people"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of pictures of the person",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
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
            $person = ['pictures' => $this->jikan->getPersonPictures(new PersonPicturesRequest($id))];
            $response = \json_decode($this->serializer->serialize($person, 'json'), true);

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
