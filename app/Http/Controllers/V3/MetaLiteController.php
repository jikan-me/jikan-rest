<?php

namespace App\Http\Controllers\V3;

class MetaLiteController extends Controller
{

    private const VALID_REQUESTS = ['status', 'requests'];
    private const VALID_TYPE = ['anime', 'manga', 'character', 'people', 'person', 'search', 'top', 'season'];
    private const VALID_PERIOD = ['today', 'weekly', 'monthly'];
    private const LIMIT = 1000;

    private $request;
    private $type;
    private $period;

    public function request($request, $type = null, $period = null) {

        if (!in_array($request, self::VALID_REQUESTS)) {
            return response()->json([
                'error' => 'Invalid or incomplete endpoint'
            ], 400);
        }
        $this->request = $request;

        if (!is_null($type)) {
            if (!in_array($type, self::VALID_TYPE)) {
                return response()->json([
                    'error' => 'Invalid type request'
                ], 400);
            }
            $this->type = $type;
        }

        if (!is_null($period)) {
            if (!in_array($period, self::VALID_PERIOD)) {
                return response()->json([
                    'error' => 'Invalid period request'
                ], 400);
            }
            $this->period = $period;
        }

        return response()->json(
            $this->{$this->request}()
        );

    }

    private function status() {
        $info = app('redis')->info();

        return [
            'cached_requests' => app('redis')->dbsize(),
            'requests_today' => count(app('redis')->keys('requests:today:*')),
            'requests_this_week' => count(app('redis')->keys('requests:weekly:*')),
            'requests_this_month' => count(app('redis')->keys('requests:monthly:*')),
            'connected_clients' => $info['Clients']['connected_clients'],
            'total_connections_received' => $info['Stats']['total_connections_received'],
            //'db_keys' => $info['Keyspace']['db0']['keys'],
            //'db_expires' => $info['Keyspace']['db0']['expires'],
            //'db_avg_ttl' => $info['Keyspace']['db0']['avg_ttl'],
        ];
    }

    private function requests() {

        if (is_null($this->type)) {
           return  ['error' => 'Missing type'];
        }

        if (is_null($this->period)) {
            return ['error' => 'Missing period'];
        }

        $requests = [];
        $data = app('redis')->keys('requests:'.$this->period.':*'.$this->type.'*');

        foreach ($data as $hashKey) {
            $requests[explode(":", $hashKey)[2]] = (int) app('redis')->get($hashKey);
        }

        arsort($requests);

        return array_slice($requests, 0, self::LIMIT);
    }

}
