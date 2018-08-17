<?php

namespace App\Http\Controllers\V3;

use Jikan\Request\Schedule\ScheduleRequest;

class ScheduleController extends Controller
{

    private const VALID_DAYS = [
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

    public function main(?string $day = null)
    {
        if (null !== $day && !\in_array(strtolower($day), self::VALID_DAYS, true)) {
            return response()->json([
                'error' => 'Bad Request',
            ])->setStatusCode(400);
        }

        $schedule = $this->jikan->getSchedule(new ScheduleRequest());

        if (null !== $day) {
            $schedule = [
                strtolower($day) => $schedule->{'get'.ucfirst(strtolower($day))}(),
            ];
        }

        return response($this->serializer->serialize($schedule, 'json'));
    }
}
