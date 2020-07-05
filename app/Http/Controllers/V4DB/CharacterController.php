<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Character;
use App\Http\HttpHelper;
use App\Http\HttpResponse;
use App\Http\Resources\V4\PicturesResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\Request\Anime\AnimePicturesRequest;
use Jikan\Request\Character\CharacterRequest;
use Jikan\Request\Character\CharacterPicturesRequest;
use MongoDB\BSON\UTCDateTime;

class CharacterController extends Controller
{
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
                    ->where('request_hash', $this->fingerprint)
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
