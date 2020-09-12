<?php

namespace App\Http\Controllers\V3;

use App\Http\HttpHelper;
use App\Http\HttpResponse;
use App\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\Exception\BadResponseException;
use Jikan\Request\Manga\MangaPicturesRequest;
use Jikan\Request\Person\PersonRequest;
use Jikan\Request\Person\PersonPicturesRequest;
use MongoDB\BSON\UTCDateTime;

class PersonController extends Controller
{
    public function main(Request $request, int $id)
    {
        $results = Person::query()
            ->where('mal_id', $id)
            ->get();

        $isExpired = $this->isExpired($request, $results);
        $expiryTtl = $this->getExpiryTtl($request, $results);
        $cached = true;

        if (
            $results->isEmpty()
            || $isExpired
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

        $response = (new \App\Http\Resources\V3\PersonResource(
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
            $person = ['pictures' => $this->jikan->getPersonPictures(new PersonPicturesRequest($id))];
            $response = \json_decode($this->serializer->serialize($person, 'json'), true);

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
