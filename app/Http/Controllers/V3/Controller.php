<?php

namespace App\Http\Controllers\V3;

use App\Providers\SerializerFactory;
use App\Providers\SerializerServiceProdivder;
use App\Providers\SerializerServiceProviderV3;
use Jikan\Jikan;
use Jikan\MyAnimeList\MalClient;
use JMS\Serializer\Serializer;
use Laravel\Lumen\Routing\Controller as BaseController;


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
     * AnimeController constructor.
     *
     * @param Serializer      $serializer
     * @param MalClient $jikan
     */
    public function __construct(MalClient $jikan)
    {
        $this->serializer = SerializerFactory::createV3();
        $this->jikan = $jikan;
    }
}
