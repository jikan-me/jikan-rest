<?php

namespace App\Http\Controllers;

use Jikan\Exception\ParserException;
use Jikan\MyAnimeList\MalClient as Jikan;
use Jikan\Request\Anime\AnimeRequest;
use JMS\Serializer\Serializer;

class AnimeController extends Controller
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var Jikan
     */
    private $jikan;

    /**
     * AnimeController constructor.
     *
     * @param Serializer $serializer
     * @param Jikan      $jikan
     */
    public function __construct(Serializer $serializer, Jikan $jikan)
    {
        $this->serializer = $serializer;
        $this->jikan = $jikan;
    }

    public function request(int $id, $request = null, $requestArg = null)
    {
        try {
            $anime = $this->jikan->getAnime(
                new AnimeRequest($id)
            );

            return response(
                $this->serializer->serialize($anime, 'json')
            );

        } catch (ParserException $e) {
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ]
            );
        }
    }
}
