<?php

namespace App\Http\Controllers;

class MetaLiteController extends Controller
{

    private const VALID_REQUESTS = ['status', 'requests'];
    private const VALID_TYPE = ['anime', 'manga', 'character', 'people', 'person', 'search', 'top', 'season'];
    private const VALID_PERIOD = ['today', 'weekly', 'monthly'];
    private const LIMIT = 10000;

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

    private function status() {}

    private function requests() {

        if (is_null($this->type)) {
            return response()->json([
                'error' => 'Missing type'
            ], 400);
        }

        if (is_null($this->period)) {
            return response()->json([
                'error' => 'Missing period'
            ], 400);
        }


        $requests = [];
        $data = app('redis')->keys('requests:'.$this->period.':*'.$this->type.'*');

        foreach ($data as $hashKey) {
            $requests[explode(":", $hashKey)[2]] = (int) app('redis')->get($hashKey);
        }

        return $requests;
    }

}
