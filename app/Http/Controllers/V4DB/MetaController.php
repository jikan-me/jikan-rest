<?php

namespace App\Http\Controllers\V4DB;

class MetaController extends Controller
{
    public function status()
    {
        $info = app('redis')->info();

        return response()->json([
            'cached_requests' => count(app('redis')->keys('request:*')),
            'requests_today' => count(app('redis')->keys('requests:today:*')),
            'requests_this_week' => count(app('redis')->keys('requests:weekly:*')),
            'requests_this_month' => count(app('redis')->keys('requests:monthly:*')),
            'connected_clients' => $info['Clients']['connected_clients'],
            'total_connections_received' => $info['Stats']['total_connections_received'],
        ]);
    }

    public function requests($type, $period, $offset = 0)
    {
        if (!\in_array($type, [
            'anime', 'manga', 'character', 'person', 'people', 'search', 'top', 'season', 'schedule', 'user', 'producer', 'magazine', 'genre'
        ])) {
            return response()->json([
                'error' => 'Bad Request'
            ], 400);
        }

        if (!\in_array($period, ['today', 'weekly', 'monthly'])) {
            return response()->json([
                'error' => 'Bad Request'
            ], 400);
        }

        $requests = [];
        $data = app('redis')->keys("requests:{$period}:*{$type}*");

        foreach ($data as $key) {
            $requests[explode(":", $key)[2]] = (int) app('redis')->get($key);
        }

        arsort($requests);

        return response()->json(
            \array_slice($requests, $offset, 1000)
        );
    }
}
