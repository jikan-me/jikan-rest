<?php

namespace App\Http\Controllers;

use Jikan\Request\Seasonal\SeasonalRequest;

class SeasonController extends Controller
{
    public function main(int $year = null, string $season = null)
    {
        $person = $this->jikan->getSeasonal(new SeasonalRequest($year, $season));
        return response($this->serializer->serialize($person, 'json'));
    }
}
