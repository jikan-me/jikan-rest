<?php

namespace App\Http\Controllers\V2;

use Jikan\Request\Character\CharacterRequest;
use Jikan\Request\Character\CharacterPicturesRequest;

class CharacterController extends Controller
{
    public function _main($id) {
        $character = $this->jikan->getCharacter(new CharacterRequest($id));

        // backwards compatibility
        $character = json_decode(
            $this->serializer->serialize($character, 'json'),
            true
        );

        $character['nicknames'] = empty($character['nicknames']) ? null : implode(",", $character['nicknames']);;

        return $character;
    }

    public function main(int $id)
    {
        $character = $this->_main($id);

        return response($character);
    }

    public function pictures(int $id)
    {
        $character = $this->_main($id);
        $pictures = ['image' =>$this->jikan->getCharacterPictures(new CharacterPicturesRequest($id))];
        $pictures = json_decode(
            $this->serializer->serialize($pictures, 'json'),
            true
        );

        foreach($pictures['image'] as $key => $value) {
            $pictures['image'][$key] = $value['small'];
        }


        return response(
            array_merge(
                $character,
                $pictures
            )
        );
    }
}
