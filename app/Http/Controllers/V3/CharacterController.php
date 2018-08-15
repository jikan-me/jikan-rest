<?php

namespace App\Http\Controllers\V3;

use Jikan\Request\Character\CharacterRequest;
use Jikan\Request\Character\CharacterPicturesRequest;

class CharacterController extends Controller
{
    public function main(int $id)
    {
        $character = $this->jikan->getCharacter(new CharacterRequest($id));
        return response($this->serializer->serialize($character, 'json'));
    }

    public function pictures(int $id)
    {
        $character = $this->jikan->getCharacterPictures(new CharacterPicturesRequest($id));
        return response($this->serializer->serialize($character, 'json'));
    }
}
