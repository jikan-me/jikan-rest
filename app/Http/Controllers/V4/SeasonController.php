<?php

namespace App\Http\Controllers\V4;

use Jikan\Request\Seasonal\SeasonalRequest;
use Jikan\Request\SeasonList\SeasonListRequest;

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

    public function archive()
    {
        return response(
            $this->serializer->serialize(
                ['archive' => $this->jikan->getSeasonList(new SeasonListRequest())],
                'json'
            )
        );
    }

    public function later()
    {
        $season = $this->jikan->getSeasonal(new SeasonalRequest(null, null, true));
        return response($this->serializer->serialize($season, 'json'));
    }
}
