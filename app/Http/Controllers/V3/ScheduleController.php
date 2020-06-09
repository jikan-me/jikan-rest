<?php

namespace App\Http\Controllers\V3;

use App\Http\HttpHelper;
use Illuminate\Support\Facades\DB;
use Jikan\Request\Schedule\ScheduleRequest;
use phpDocumentor\Reflection\Types\Self_;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    private const VALID_FILTERS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
        'other',
        'unknown',
    ];

    private const VALID_DAYS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    private $request;
    private $day;

    public function main(Request $request, ?string $day = null)
    {
        $this->request = $request;

        if (!is_null($day)) {
            $this->day = strtolower($day);
        }

        if (null !== $day && !\in_array($this->day, self::VALID_FILTERS, true)) {
            return response()->json([
                'error' => 'Bad Request',
            ])->setStatusCode(400);
        }

        $results = DB::table('anime')
            ->orderBy('members')
            ->where('type', 'TV')
            ->where('status', 'Currently Airing');

        $results = $results
            ->get([
                'mal_id', 'url', 'title', 'image_url', 'synopsis', 'type', 'airing.from', 'episodes', 'members', 'genres', 'source', 'producers', 'score', 'licensors', 'rating', 'broadcast'
            ]);


        $items = $this->applyBackwardsCompatibility($results);

        return response()->json($items);

//        $schedule = $this->jikan->getSchedule(new ScheduleRequest());
//        if (null !== $day) {
//            $schedule = [
//                strtolower($day) => $schedule->{'get'.ucfirst(strtolower($day))}(),
//            ];
//        }
//
//        return response($this->serializer->serialize($schedule, 'json'));
    }

    private function applyBackwardsCompatibility($data)
    {
        $fingerprint = HttpHelper::resolveRequestFingerprint($this->request);

        $meta = [
            'request_hash' => $fingerprint,
            'request_cached' => true,
            'request_cache_expiry' => 0,
        ];

        $results = [
            'monday' => [],
            'tuesday' => [],
            'wednesday' => [],
            'thursday' => [],
            'friday' => [],
            'saturday' => [],
            'sunday' => [],
            'other' => [],
            'unknown' => []
        ];

        $items = $data->all() ?? [];
        foreach ($items as &$item) {

            if (isset($item['aired']['from'])) {
                $item['airing_start'] = $item['aired']['from'];
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

            unset($item['_id'], $item['oid'], $item['expiresAt'], $item['aired']);

            if (!isset($item['broadcast'])) {
                continue;
            }

            foreach (self::VALID_FILTERS as $day) {
                $broadcastDay = ucfirst($day);

                if (!isset($item['broadcast'])) {
                    continue 2;
                }

                if (preg_match("~^{$broadcastDay}~", $item['broadcast'])) {
                    unset($item['broadcast']);
                    $results[$day][] = $item;
                }
            }
        }

        foreach ($results as &$day) {
            $day = array_reverse($day);
        }

        if (!is_null($this->day)) {
            $results = [$this->day => $results[$this->day]];
        }

        return $meta+$results;
    }
}
