<?php

namespace App\Http\Controllers;

use Jikan\Request\Anime\AnimeRequest;

class AnimeController extends Controller
{
    public function request(int $id, $request = null, $requestArg = null)
    {
        $anime = $this->jikan->getAnime(new AnimeRequest($id));
        return response($this->serializer->serialize($anime, 'json'));
    }
}
