<?php

namespace App\Http\Controllers\V3;

use App\Http\HttpHelper;
use App\Providers\SerializerFactory;
use App\Providers\SerializerServiceProdivder;
use App\Providers\SerializerServiceProviderV3;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\Jikan;
use Jikan\MyAnimeList\MalClient;
use JMS\Serializer\Context;
use JMS\Serializer\Serializer;
use Laravel\Lumen\Routing\Controller as BaseController;
use MongoDB\BSON\UTCDateTime;

class Controller extends BaseController
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var MalClient
     */
    protected $jikan;

    protected $fingerprint;

    /**
     * AnimeController constructor.
     *
     * @param Serializer      $serializer
     * @param MalClient $jikan
     */
    public function __construct(Request $request, MalClient $jikan)
    {
        $this->serializer = SerializerFactory::createV3();
        $this->jikan = app('JikanParser');
        $this->fingerprint = HttpHelper::resolveRequestFingerprint($request);

    }

    protected function isExpired($request, $results) : bool
    {
        $lastModified = $this->getLastModified($results);

        if ($lastModified === null) {
            return true;
        }

        $routeName = HttpHelper::getRouteName($request);
        $expiry = (int) config("controller.{$routeName}.ttl") + $lastModified;

        if (time() > $expiry) {
            return true;
        }

        return false;
    }

    protected function getExpiryTtl($request, $results) : int
    {
        $lastModified = $this->getLastModified($results);

        if ($lastModified === null) {
            return 0;
        }

        return $lastModified - time();
    }

    protected function getExpiry($results, $request)
    {
        $modifiedAt = $this->getLastModified($results);

        $routeName = HttpHelper::getRouteName($request);
        return (int) config("controller.{$routeName}.ttl") + $modifiedAt;
    }

    protected function getTtl($results, $request)
    {
        $routeName = HttpHelper::getRouteName($request);
        return (int) config("controller.{$routeName}.ttl");
    }

    protected function getLastModified($results) : ?int
    {
        if (is_array($results->first())) {
            return (int) $results->first()['modifiedAt']->toDateTime()->format('U');
        }

        if (is_object($results->first())) {
            return (int) $results->first()->modifiedAt->toDateTime()->format('U');
        }

        return null;
    }

    protected function serialize($data) : array
    {
        return \json_decode(
            $this->serializer->serialize($data, 'json')
        );
    }

    protected function getRouteName($request) : string
    {
        return HttpHelper::getRouteName($request);
    }

    protected function getRouteTable($request) : string
    {
        return config("controller.{$this->getRouteName($request)}.table_name");
    }

    protected function prepareResponse($response, $results, $request)
    {
        return $response
            ->header('X-Request-Fingerprint', $this->fingerprint)
            ->setTtl($this->getTtl($results, $request))
            ->setExpires(
                (new \DateTimeImmutable())->setTimestamp(
                    $this->getExpiry($results, $request)
                )
            )
            ->setLastModified(
                (new \DateTimeImmutable())->setTimestamp(
                    $this->getLastModified($results)
                )
            );
    }

    protected function bcMeta($response, $fingerprint, $cached, $cacheTtl) : array
    {
        return [
            'request_hash' => $fingerprint,
            'request_cached' => $cached,
            'request_cache_expiry' => $cacheTtl
        ] + $response;
    }

    protected function updateCache($request, $results, $response)
    {
        // If resource doesn't exist, prepare meta
        if ($results->isEmpty()) {
            $meta = [
                'createdAt' => new UTCDateTime(),
                'modifiedAt' => new UTCDateTime(),
                'request_hash' => $this->fingerprint
            ];
        }

        // Update `modifiedAt` meta
        $meta['modifiedAt'] = new UTCDateTime();
        // join meta data with response
        $response = $meta + $response;

        // insert cache if resource doesn't exist
        if ($results->isEmpty()) {
            DB::table($this->getRouteTable($request))
                ->insert($response);
        }

        // update cache if resource exists
        if ($this->isExpired($request, $results)) {
            DB::table($this->getRouteTable($request))
                ->where('request_hash', $this->fingerprint)
                ->update($response);
        }

        // return results
        return DB::table($this->getRouteTable($request))
            ->where('request_hash', $this->fingerprint)
            ->get();
    }

    /**
     * @param array $response
     * @return array
     */
//    protected function prepareResponse(Request $request, array $response) : array
//    {
//        $this->request = $request;
//        $this->response = $response;
//
//        unset($this->response['_id']);
//
//        $this->mutation();
//
//        $this->response = ['data' => $this->response];
//        return $this->response;
//    }

    /**
     * @param Request $request
     * @param array $response
     * @return array
     */
    private function mutation() : void
    {
        $requestType = HttpHelper::requestType($this->request);

        if (($requestType === 'anime' || $requestType === 'manga')) {

            // Fix JSON response for empty related object
            if (isset($this->response['related']) && \count($this->response['related']) === 0) {
                $this->response['related'] = new \stdClass();
            }

            if (isset($this->response['related']) && !is_object($this->response['related']) && !empty($this->response['related'])) {
                $relation = [];
                foreach ($this->response['related'] as $relationType => $related) {
                    $relation[] = [
                        'relation' => $relationType,
                        'items' => $related
                    ];
                }
                $this->response['related'] = $relation;
            }
        }
    }
}
