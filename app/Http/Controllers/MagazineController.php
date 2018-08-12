<?php

namespace App\Http\Controllers;

use Jikan\Request\Magazine\MagazineRequest;

class MagazineController extends Controller
{
    public function main(int $id, int $page = 1)
    {
        $producer = $this->jikan->getMagazine(new MagazineRequest($id, $page));
        return response($this->serializer->serialize($producer, 'json'));
    }
}
