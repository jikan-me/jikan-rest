<?php

namespace App\Http\Controllers\V3;

use Jikan\Request\Magazine\MagazineRequest;

class MagazineController extends Controller
{
    public function main(int $id, int $page = 1)
    {
        $magazine = $this->jikan->getMagazine(new MagazineRequest($id, $page));
        return response($this->serializer->serialize($magazine, 'json'));
    }
}
