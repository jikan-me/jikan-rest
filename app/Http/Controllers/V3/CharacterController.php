<?php

namespace App\Http\Controllers\V3;

use App\Character;
use App\Http\HttpHelper;
use App\Http\HttpResponse;
use App\Http\Resources\V4\PicturesResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\Request\Character\CharacterPicturesRequest;
use MongoDB\BSON\UTCDateTime;

class CharacterController extends Controller
{
    public function main(Request $request, int $id)
    {
        $results = Character::query()
            ->where('mal_id', $id)
            ->get();

        $isExpired = $this->isExpired($request, $results);
        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;

        if (
            $results->isEmpty()
            || $isExpired
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

        $response = (new \App\Http\Resources\V3\CharacterResource(
            $results->first()
        ))->toArray($request);

        return $this->prepareResponse(
            response(
                $this->bcMeta($response, $this->fingerprint, $cached, $expiryTtl)
            ),
            $results,
            $request
        );
    }

    public function pictures(Request $request, int $id)
    {
        $results = DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();

        $isExpired = $this->isExpired($request, $results);
        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;


        if (
            $results->isEmpty()
            || $isExpired
        ) {
            $character = ['pictures' => $this->jikan->getCharacterPictures(new CharacterPicturesRequest($id))];
            $response = \json_decode($this->serializer->serialize($character, 'json'), true);

            $results = $this->updateCache($request, $results, $response);
        }

        $response = (new \App\Http\Resources\V3\PicturesResource(
            $results->first()
        ))->toArray($request);

        return $this->prepareResponse(
            response(
                $this->bcMeta($response, $this->fingerprint, $cached, $expiryTtl)
            ),
            $results,
            $request
        );
    }
}
