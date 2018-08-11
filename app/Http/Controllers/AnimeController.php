<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client as GuzzleClient;
use Jikan\Jikan;

class AnimeController extends Controller
{

    public $id;
    public $extend;
    public $extendArgs;

    private const VALID_REQUESTS = [
        'episodes',
        'characters_staff',
        'news',
        'forum',
        'pictures',
        'videos',
        'stats',
        'moreinfo',
    ];

    public function request(int $id, $request = null, $requestArg = null)
    {


        try {
            $jikan = new Jikan();
            $response = $jikan->Anime($id);
        } catch (\Jikan\Exception\ParserException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ]);
        }

        $serializer = (\JMS\Serializer\SerializerBuilder::create())
            ->addMetadataDir(__DIR__.'/../../../resources/serializer')
            ->build();
        $json = $serializer->serialize($response, 'json');

        return response(
            $json
        );

    }

}
