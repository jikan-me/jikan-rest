<?php

namespace App\Http\Controllers;

class MetaController extends Controller
{

    private const VALID_REQUESTS = ['status', 'requests'];
    private const VALID_TYPE = ['anime', 'manga', 'character', 'people', 'person', 'search', 'top', 'season'];
    private const VALID_PERIOD = ['today', 'weekly', 'monthly'];
    private const LIMIT = 5;

    private $request;
    private $type;
    private $period;
    private $page;

    public function request($request, $type = null, $period = null, $page = 0) {

        if (!is_null($request)) {
            if (!in_array($request, self::VALID_REQUESTS)) {
                return response()->json([
                    'error' => 'Invalid or incomplete endpoint'
                ], 400);
            }

            $this->request = $request;
        }

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
                ]);
            }

            $this->period = $period;
        }

        if (!is_null($page)) {
            $this->page = $page - 1;

            if ($this->page < 0) {
                $this->page = 0;
            }
        }

        if (in_array($request, self::VALID_REQUESTS)) {
            return response()->json($this->{$request}());
        }


/*        $key = "meta:requests:".date("m-o");

        if (!app('redis')->exists($key)) {
            return response()->json([
                'error' => 'Requests for this period do not exist'
            ], 404);
        }*/

    }

    public function status() {
        $key = "meta:requests:".date("m-o");
        $requests_this_month = 0;
        $requests_this_week = 0;
        $requests_today = 0;

        $requests = [];
        if (app('redis')->exists($key)) {
            $requests = app('redis')->hGetAll($key);
        }

        $today = strtotime('today UTC');
        $last_week = strtotime("-1 week");
        $now = time();

        foreach ($requests as $time => $data) {

            $data = json_decode($data, true);
            $count = count($data);

            $requests_this_month += $count;

            if ($time >= $last_week && $time <= $now) {
                $requests_this_week += $count;
            }

            if ($time >= $today && $time <= $now) {
                $requests_today += $count;
            }

        }

        $info = app('redis')->info();


        return response()->json([
            'cached_requests' => app('redis')->dbsize(),
            'requests_today' => $requests_today,
            'requests_this_week' => $requests_this_week,
            'requests_this_month' => $requests_this_month,
            'connected_clients' => $info['Clients']['connected_clients'],
            'total_connections_received' => $info['Stats']['total_connections_received'],
            'db_keys' => $info['Keyspace']['db0']['keys'],
            'db_expires' => $info['Keyspace']['db0']['expires'],
            'db_avg_ttl' => $info['Keyspace']['db0']['avg_ttl'],
        ]);
    }

    public function requests() {

        $requests = [];
        $requestsHashKey = app('redis')->sort('requests', [
            'by' => 'requests:*->time',
            'sort' => 'desc'
        ]);

        foreach ($requestsHashKey as $value) {
            $data = app('redis')->hMGet($value, ['time', 'request', 'request_type']);

            if ($data[2] != $this->type) {
                continue;
            }

            if (!isset($request[$data[1]])) {
                $request[$data[1]] = [];
            }

            $request[$data[1]][] = (int) $data[0];
        }

        return array_slice($request, 0, self::LIMIT);

    }
}
