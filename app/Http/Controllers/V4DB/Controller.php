<?php

namespace App\Http\Controllers\V4DB;

use App\Contracts\Mediator;
use App\Http\HttpHelper;
use App\Providers\SerializerFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\MyAnimeList\MalClient;
use JMS\Serializer\Serializer;
use Laravel\Lumen\Routing\Controller as BaseController;
use MongoDB\BSON\UTCDateTime;
use OpenApi\Annotations as OA;

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
     *         description="Jikan REST API",
     *         url="https://api.jikan.moe/v4",
     *     ),
     *     @OA\ExternalDocumentation(
     *         description="About",
     *         url="https://jikan.moe"
     *     ),
     * )
     */

    /**
     * @var Request
     */
    private $request;

    /**
     * @var array
     */
    private array $response;

    protected Mediator $mediator;

    /**
     * AnimeController constructor.
     *
     * @param Mediator $mediator
     */
    public function __construct(Mediator $mediator)
    {
        $this->mediator = $mediator;
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
