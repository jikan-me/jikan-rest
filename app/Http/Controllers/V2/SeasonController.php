<?php

namespace App\Http\Controllers\V2;


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

        // backwards compatibility
        $season = $this->jikan->getSeasonal(new SeasonalRequest($year, $season));
        $season = json_decode(
            $this->serializer->serialize($season, 'json'),
            true
        );

        foreach ($season['season'] as &$item) {
            $item['continued'] = $item['continuing'];
            unset($item['continuing']);
        }

        return $season;
    }
}
