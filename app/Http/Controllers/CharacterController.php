<?php

namespace App\Http\Controllers;

use Jikan\Request\Character\CharacterRequest;
use Jikan\Request\Character\CharacterPicturesRequest;

class CharacterController extends Controller
{
    public function main(int $id)
    {
        $manga = $this->jikan->getCharacter(new CharacterRequest($id));
        return response($this->serializer->serialize($manga, 'json'));
    }

    public function pictures(int $id)
    {
        $manga = $this->jikan->getCharacterPictures(new CharacterPicturesRequest($id));
        return response($this->serializer->serialize($manga, 'json'));
    }
}
