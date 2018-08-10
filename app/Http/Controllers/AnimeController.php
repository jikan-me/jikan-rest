<?php

namespace App\Http\Controllers;

use http\Env\Response;
use Illuminate\Http\Request;
use App\Exceptions\Handler as Handler;
use Jikan\Jikan;
use Lazer\Classes\Database as Lazer;
use GuzzleHttp\Client as GuzzleClient;

class AnimeController extends Controller
{

    public $id;
    public $extend;
    public $extendArgs;

    private const VALID_REQUESTS = ['episodes', 'characters_staff', 'news', 'forum', 'pictures', 'videos', 'stats', 'moreinfo'];

    public function request(int $id, $request = null, $requestArg = null) {

        $this->guzzle = new GuzzleClient;

        try {
            $jikan = new Jikan($this->guzzle);
            $this->response = $jikan->Anime($id);
        } catch (\Jikan\Exception\ParserException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        $json = $serializer->serialize($this->response, 'json');

        return response(
          $json
        );

    }

}
