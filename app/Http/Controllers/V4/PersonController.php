<?php

namespace App\Http\Controllers\V4;

use Jikan\Request\Person\PersonRequest;
use Jikan\Request\Person\PersonPicturesRequest;

class PersonController extends Controller
{
    public function main(int $id)
    {
        $person = $this->jikan->getPerson(new PersonRequest($id));
        return response($this->serializer->serialize($person, 'json'));
    }

    public function pictures(int $id)
    {
        $person = ['pictures' => $this->jikan->getPersonPictures(new PersonPicturesRequest($id))];
        return response($this->serializer->serialize($person, 'json'));
    }
}
