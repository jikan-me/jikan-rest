<?php

namespace App\Http\Controllers\V4DB;

use App\Http\HttpHelper;
use App\Providers\SerializerFactory;
use App\Providers\SerializerServiceProdivder;
use App\Providers\SerializerServiceProviderV3;
use Illuminate\Http\Request;
use Jikan\Jikan;
use Jikan\MyAnimeList\MalClient;
use JMS\Serializer\Context;
use JMS\Serializer\Serializer;
use Laravel\Lumen\Routing\Controller as BaseController;

/**
 * Class Controller
 * @package App\Http\Controllers\V4DB
 */
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

    /**
     * @var Request
     */
    private $request;

    /**
     * @var array
     */
    private $response;

    /**
     * AnimeController constructor.
     *
     * @param Serializer      $serializer
     * @param MalClient $jikan
     */
    public function __construct(MalClient $jikan)
    {
        $this->serializer = SerializerFactory::createV4();
        $this->jikan = $jikan;
    }

    /**
     * @param array $response
     * @return array
     */
    protected function prepareResponse(Request $request, array $response) : array
    {
        $this->request = $request;
        $this->response = $response;

        unset($this->response['_id']);

        $this->mutation();

        $this->response = ['data' => $this->response];
        return $this->response;
    }

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
