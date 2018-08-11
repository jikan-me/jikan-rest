<?php

namespace App\Http\Controllers;

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
    public function __construct(Serializer $serializer, MalClient $jikan)
    {
        $this->serializer = $serializer;
        $this->jikan = $jikan;
    }
}
