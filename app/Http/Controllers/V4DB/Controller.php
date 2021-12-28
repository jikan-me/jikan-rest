<?php

namespace App\Http\Controllers\V4DB;

use App\Http\HttpHelper;
use App\Providers\SerializerFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\MyAnimeList\MalClient;
use JMS\Serializer\Serializer;
use Laravel\Lumen\Routing\Controller as BaseController;
use MongoDB\BSON\UTCDateTime;

/**
 * Class Controller
 * @package App\Http\Controllers\V4DB
 */
class Controller extends BaseController
{

    /**
     * @OA\OpenApi(
     *     @OA\Info(
     *         version="4.0.0",
     *         title="Jikan API",
     *         description=API_DESCRIPTION,
     *         termsOfService="https://jikan.moe/terms",
     *
     *         @OA\Contact(
     *             name="API Support (Discord)",
     *             url="http://discord.jikan.moe"
     *         ),
     *         @OA\License(
     *             name="MIT",
     *             url="https://github.com/jikan-me/jikan-rest/blob/master/LICENSE"
     *         )
     *     ),
     *     @OA\Server(
     *         description="Jikan REST API Beta",
     *         url="https://api.jikan.moe/v4",
     *     ),
     *     @OA\ExternalDocumentation(
     *         description="About",
     *         url="https://jikan.moe"
     *     ),
     * )
     */

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var MalClient
     */
    protected $jikan;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var array
     */
    private $response;

    protected $expired = false;

    protected $fingerprint;

    /**
     * AnimeController constructor.
     *
     * @param Serializer      $serializer
     * @param MalClient $jikan
     */
    public function __construct(Request $request, MalClient $jikan)
    {
        $this->serializer = SerializerFactory::createV4();
        $this->jikan = $jikan;
        $this->fingerprint = HttpHelper::resolveRequestFingerprint($request);
    }

    protected  function isExpired($request, $results) : bool
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
                        'entry' => $related
                    ];
                }
                $this->response['related'] = $relation;
            }
        }
    }

}
