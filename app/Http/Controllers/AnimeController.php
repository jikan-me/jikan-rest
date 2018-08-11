<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Jikan\Jikan;
use GuzzleHttp\Client as GuzzleClient;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder;
use Jikan\Model\Common\MalUrl;

class AnimeController extends Controller
{

    public $id;
    public $extend;
    public $extendArgs;

    private const VALID_REQUESTS = ['episodes', 'characters_staff', 'news', 'forum', 'pictures', 'videos', 'stats', 'moreinfo'];

    public function request(int $id, $request = null, $requestArg = null) {


        try {
            $jikan = new Jikan();
            $this->response = $jikan->Anime($id);
        } catch (\Jikan\Exception\ParserException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }


        $serializer = (new SerializerBuilder())
            ->addMetadataDir(__DIR__.'/../../../storage/app/metadata')
            ->configureHandlers(function (HandlerRegistry $registry) {
                $registry->registerHandler('serialization', MalUrl::class, 'json',
                    function ($visitor, MalUrl $obj, array $type) {
                        $obj->getUrl();
                    }
                );
            })
            ->build();

        $json = $serializer->serialize($this->response, 'json');

        return response(
          $json
        );

    }

}
