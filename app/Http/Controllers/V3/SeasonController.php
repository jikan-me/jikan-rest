<?php

namespace App\Http\Controllers\V3;

use App\Http\HttpHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    private $request;
    private $season;
    private $year;

    public function main(Request $request, ?int $year = null, ?string $season = null)
    {
        $this->request = $request;

        if (!is_null($season) && !\in_array(strtolower($season), self::VALID_SEASONS)) {
            return response()->json([
                'error' => 'Bad Request'
            ])->setStatusCode(400);
        }

        if (!is_null($season)) {
            $this->season = ucfirst(
                strtolower($season)
            );
        }

        if (!is_null($year)) {
            $this->year = (int) $year;
        }

        if (is_null($season) && is_null($year)) {
            list($this->season, $this->year) = $this->getSeasonStr();
        }

        $results = DB::table('anime')
            ->where('premiered', "{$this->season} $this->year")
            ->orderBy('members', 'desc')
            ->get([
                'mal_id', 'url', 'title', 'image_url', 'synopsis', 'type', 'airing_start', 'episodes', 'members', 'genres', 'source', 'producers', 'score', 'licensors', 'rating'
            ]);

        $items = $this->applyBackwardsCompatibility($results, 'anime');

        return response($items);

//        $season = $this->jikan->getSeasonal(new SeasonalRequest($year, $season));
//
//        return response($this->serializer->serialize($season, 'json'));
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

    public function later(Request $request)
    {
        $this->request = $request;
        $nextYear =   (new \DateTime(null, new \DateTimeZone('Asia/Tokyo')))
            ->modify('+1 year')
            ->format('Y');

        $results = DB::table('anime')
            ->where('status', 'Not yet aired')
            ->where('premiered', 'like', "%{$nextYear}%")
            ->orderBy('members', 'desc')
            ->get([
                'mal_id', 'url', 'title', 'image_url', 'synopsis', 'type', 'airing_start', 'episodes', 'members', 'genres', 'source', 'producers', 'score', 'licensors', 'rating'
            ]);

        $this->season = 'Later';
        $items = $this->applyBackwardsCompatibility($results, 'anime');

        return response($items);

//        $season = $this->jikan->getSeasonal(new SeasonalRequest(null, null, true));
//        return response($this->serializer->serialize($season, 'json'));
    }

    private function applyBackwardsCompatibility($data, $type)
    {
        $fingerprint = HttpHelper::resolveRequestFingerprint($this->request);

        $meta = [
            'request_hash' => $fingerprint,
            'request_cached' => true,
            'request_cache_expiry' => 0,
            'season_name' => $this->season,
            'season_year' => $this->year
        ];

        $items = $data->all() ?? [];
        foreach ($items as &$item) {

            if (isset($item['aired']['from'])) {
                $item['airing_start'] = $item['aired']['from'];
            }

            if (isset($item['published']['from'])) {
                $item['publishing_start'] = $item['aired']['from'];
            }

            if (isset($item['licensors'])) {
                $licensors = [];
                foreach ($item['licensors'] as $licensor) {
                    $licensors[] = $licensor['name'];
                }

                $item['licensors'] = $licensors;
            }


            $item['kids'] = false;
            if (isset($item['rating'])) {
                if ($item['rating'] === 'G - All Ages' || $item['rating'] === 'PG - Children') {
                    $item['kids'] = true;
                }
            }

            $item['r18'] = false;
            if (isset($item['rating'])) {
                if ($item['rating'] === 'R+ - Mild Nudity' || $item['rating'] === 'Rx - Hentai') {
                    $item['r18'] = true;
                }
            }

            // @todo : no way of knowing this at the moment; defaulted to false
            $item['continuing'] = false;

            unset($item['_id'], $item['oid'], $item['expiresAt'], $item['aired'], $item['rating']);
        }

        $items = [$type => $items];

        return $meta+$items;
    }

    private function getSeasonStr() : array
    {
        $date = new \DateTime(null, new \DateTimeZone('Asia/Tokyo'));

        $year = (int) $date->format('Y');
        $month = (int) $date->format('n');

        switch ($month) {
            case \in_array($month, range(1, 3)):
                return ['Winter', $year];
            case \in_array($month, range(4, 6)):
                return ['Spring', $year];
            case \in_array($month, range(7, 9)):
                return ['Summer', $year];
            case \in_array($month, range(10, 12)):
                return ['Fall', $year];
            default: throw new \Exception('Could not generate seasonal string');
        }
    }
}
