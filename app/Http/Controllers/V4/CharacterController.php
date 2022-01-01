<?php

namespace App\Http\Controllers\V4;

use App\Http\HttpHelper;
use App\Http\HttpResponse;
use App\Manga;
use Jikan\Request\Character\CharacterRequest;
use Jikan\Request\Character\CharacterPicturesRequest;
use MongoDB\BSON\UTCDateTime;

class CharacterController extends Controller
{
    public function main(int $id)
    {
        $character = $this->jikan->getCharacter(new CharacterRequest($id));
        return response($this->serializer->serialize($character, 'json'));
    }

    public function pictures(int $id)
    {
        $character = ['pictures' => $this->jikan->getCharacterPictures(new CharacterPicturesRequest($id))];
        return response($this->serializer->serialize($character, 'json'));
    }
}
