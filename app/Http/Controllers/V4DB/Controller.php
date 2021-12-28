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
     *         description="[Jikan](https://jikan.moe) is an **Unofficial** MyAnimeList API.
     *
     *         ## Information
     *         It scrapes the website to satisfy the need for a complete API - which MyAnimeList lacks.
     *     
     *         ⚡ Jikan is powered by it's awesome backers - 🙏 [Become a backer](https://www.patreon.com/jikan)
     *
     *         ### Rate Limiting
     *
     *         | Duration | Requests |
     *         |----|----|
     *         | Daily | **Unlimited** |
     *         | Per Minute | 60 requests |
     *         | Per Second | 3 requests |
     *
     *
     *         ### JSON Notes
     *         - Any property (except arrays or objects) whose value does not exist or is undetermined, will be `null`.
     *         - Any array or object property whose value does not exist or is undetermined, will be `null`.
     *         - Any `score` property whose value does not exist or is undetermined, will be `0`.
     *         - All dates and timestamps are returned in [ISO8601](https://en.wikipedia.org/wiki/ISO_8601) format and in UTC timezone
     *
     *         ### Caching
     *         By **CACHING**, we refer to the data parsed from MyAnimeList which is stored temporarily on our servers to provide better API performance.
     *
     *         All requests, by default are cached for **24 hours** except the following endpoints which have their own unique cache **Time To Live**.
     *
     *         | Request | TTL |
     *         | ---- | ---- |
     *         | All (Default) | 24 hours |
     *         | User Anime/Manga List | 5 minutes |
     *
     *
     *         The following response headers will detail cache information.
     *
     *         | Header | Remarks |
     *         | ---- | ---- |
     *         | `Expires` | Expiry unix timestamp |
     *
     *
     *         ### Allowed HTTP(s) requests
     *
     *         **Jikan REST API does not provide authenticated requests for MyAnimeList.** This means you can not use it to update your anime/manga list.
     *         Only GET requests are supported which return READ-ONLY data.
     *
     *         ### HTTP Responses
     *
     *         | HTTP Status | Remarks |
     *         | ---- | ---- |
     *         | `200 - OK` | The request was successful |
     *         | `304 - Not Modified` | You have the latest data (Cache Validation response) |
     *         | `400 - Bad Request` | You've made an invalid request. Recheck documentation |
     *         | `404 - Not Found` | The resource was not found or MyAnimeList responded with a `404` |
     *         | `405 - Method Not Allowed` | Requested Method is not supported for resource. Only `GET` requests are allowed |
     *         | `429 - Too Many Request` | You are being rate limited by Jikan or MyAnimeList is rate-limiting our servers (specified in the error response) |
     *         | `500 - Internal Server Error` | Something is not working on our end. If you see an error response with a `report_url` URL, please click on it to open an auto-generated GitHub issue |
     *         | `503 - Service Unavailable` | The service has broke. |
     *
     *
     *         ### JSON Error Response
     *
     *         ```json
     *          {
     *              'status': 404,
     *              'type': 'BadResponseException',
     *              'message': 'Resource does not exist',
     *              'error': 'Something Happened',
     *              'report_url': 'https://github.com...'
     *           }
     *         ```
     *
     *         | Property | Remarks |
     *         | ---- | ---- |
     *         | `status` | Returned HTTP Status Code |
     *         | `type` | `Exception` generated from the API |
     *         | `message` | Human-readable error message |
     *         | `error` | Error response and trace from the API |
     *         | `report_url` | Clicking this would redirect you to a generated GitHub issue. ℹ It's only returned on a parser error. |
     *
     *
     *         ### Cache Validation
     *
     *         - All requests return a `ETag` header which is an MD5 hash of the response
     *         - You can use this hash to verify if there's new or updated content by suppliying it as the value for the `If-None-Match` in your next request header
     *         - You will get a HTTP `304 - Not Modified` response if the content has not changed
     *         - If the content has changed, you'll get a HTTP `200 - OK` response with the updated JSON response
     *
     *         ![Cache Validation](https://i.imgur.com/925ozVn.png 'Cache Validation')
     *
     *         ### Disclaimer
     *
     *         - Jikan is not affiliated with MyAnimeList.net.
     *         - Jikan is a free, open-source API. Please use it responsibly.
     *
     *         ----
     *
     *         By using the API, you are agreeing to Jikan's [terms of use](https://jikan.moe/terms) policy.
     *
     *         [v3 Documentation](https://jikan.docs.apiary.io/) - [Wrappers/SDKs](https://github.com/jikan-me/jikan#wrappers) - [Report an issue](https://github.com/jikan-me/jikan-rest/issues/new)
     *         [Host your own server](https://github.com/jikan-me/jikan-rest)
     *         ",
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
