<?php

namespace App\Http\Controllers\V3;

use Jikan\Exception\BadResponseException;
use Jikan\Request\Person\PersonRequest;
use Jikan\Request\Person\PersonPicturesRequest;

class PersonController extends Controller
{
    public function main(int $id)
    {
        if ($id < 1) { // MAL INCONSISTENCY: doesn't return 404, it returns an error message with HTTP 200 instead
            throw new BadResponseException(null, 404);
        }

        $person = $this->jikan->getPerson(new PersonRequest($id));
        return response($this->serializer->serialize($person, 'json'));
    }

    public function pictures(int $id)
    {
        if ($id < 1) { // MAL INCONSISTENCY: doesn't return 404, it returns an error message with HTTP 200 instead
            throw new BadResponseException(null, 404);
        }

        $person = ['pictures' => $this->jikan->getPersonPictures(new PersonPicturesRequest($id))];
        return response($this->serializer->serialize($person, 'json'));
    }
}
