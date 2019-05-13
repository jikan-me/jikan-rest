<?php

namespace App\Http\Controllers\V2;

use Jikan\Request\Person\PersonRequest;
use Jikan\Request\Person\PersonPicturesRequest;

class PersonController extends Controller
{
    public function _main($id)
    {
        $person = $this->jikan->getPerson(new PersonRequest($id));

        // backwards compatibility
        $person = json_decode(
            $this->serializer->serialize($person, 'json'),
            true
        );

        return $person;
    }

    public function main(int $id)
    {
        $person = $this->_main($id);

        return response(
            json_encode($person)
        );
    }

    public function pictures(int $id)
    {
        $person = $this->_main($id);
        $pictures = ['image' =>$this->jikan->getPersonPictures(new PersonPicturesRequest($id))];
        $pictures = json_decode(
            $this->serializer->serialize($pictures, 'json'),
            true
        );

        foreach ($pictures['image'] as $key => $value) {
            $pictures['image'][$key] = $value['small'];
        }


        return response(
            json_encode(
                array_merge(
                    $person,
                    $pictures
                )
            )
        );
    }
}
