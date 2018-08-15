<?php

namespace App\Http\Controllers\V3;

use Jikan\Request\Seasonal\SeasonalRequest;

class SeasonController extends Controller
{

    private const VALID_SEASONS = [
        'summer',
        'spring',
        'winter',
        'fall'
    ];

    public function main(?int $year = null, ?string $season = null)
    {

        if (!is_null($season) && !\in_array(strtolower($season), self::VALID_SEASONS)) {
            return response()->json([
                'error' => 'Bad Request'
            ])->setStatusCode(400);
        }

        $season = $this->jikan->getSeasonal(new SeasonalRequest($year, $season));

        return response($this->serializer->serialize($season, 'json'));
    }
}
