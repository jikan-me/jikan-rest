<?php

namespace App\Http\Controllers\V4;

use Jikan\Request\Magazine\MagazineRequest;
use Jikan\Request\Magazine\MagazinesRequest;

class MagazineController extends Controller
{

    public function main()
    {
        $results = $this->jikan->getMagazines(new MagazinesRequest());
        return response($this->serializer->serialize($results, 'json'));
    }

    public function resource(int $id, int $page = 1)
    {
        $magazine = $this->jikan->getMagazine(new MagazineRequest($id, $page));
        return response($this->serializer->serialize($magazine, 'json'));
    }
}
