<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Http\HttpResponse;
use App\Http\Resources\V4\AnimeCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jikan\Request\Schedule\ScheduleRequest;

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

        if (null !== $this->day
            && !\in_array($this->day, self::VALID_FILTERS, true)) {
            return HttpResponse::badRequest($this->request);
        }

        $results = Anime::query()
            ->orderBy('members')
            ->where('type', 'TV')
            ->where('status', 'Currently Airing')
            ->get();

        $results = $this->mutateQueryResponse($results);

        if (!is_null($this->day)) {
            $results = [$this->day => $results[$this->day]];
        }

        return response()->json([
            'data' => $results
        ]);
    }

    private function mutateQueryResponse($results)
    {
        $return = [
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

        $items = $results->toArray() ?? [];
        foreach ($items as $item) {
            foreach (self::VALID_FILTERS as $day) {
                $broadcastDay = ucfirst($day);

                if (!isset($item['broadcast']['day'])) {
                    continue 2;
                }

                if (preg_match("~^{$broadcastDay}~", $item['broadcast']['day'])) {
                    $return[$day][] = $item;
                }
            }
        }

        foreach ($return as &$day) {
            $day = array_reverse($day);
        }

        if (!is_null($this->day)) {
            $return = [$this->day => $return[$this->day]];
        }

        return $return;
    }
}
