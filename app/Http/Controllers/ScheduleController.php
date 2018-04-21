<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\Handler as Handler;
use Jikan\Jikan;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Lazer\Classes\Database as Lazer;

class ScheduleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public $day;
    
    private $validDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    public function request($day = null) {

        $antiXss = new \voku\helper\AntiXSS();

        if (!is_null($day)) {
            $this->day = $antiXss->xss_clean($day);
            if (!in_array($this->day, $this->validDays)) {
                return response()->json(
                    ['error' => 'Invalid day request: "' . $this->day . '"'], 400
                );
            }
        }

        $this->hash = sha1('schedule' . $this->day);
        $this->response['request_hash'] = $this->hash;
        $this->response['request_cached'] = false;

        if (app('redis')->exists($this->hash)) {
            $this->response['request_cached'] = true;
            return response()->json(
                $this->response + json_decode(app('redis')->get($this->hash), true)
            );
        }

        $jikan = new Jikan;

        try {

            $jikan->Schedule();

        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            return response()->json(
                ['error' => $e->getMessage()], 404
            );
        }

        if (empty($jikan->response) || $jikan->response === false) {
            return response()->json(['error' => 'MyAnimeList Rate Limiting reached. Slow down!'], 429);
        }

        if (!is_null($this->day)) {
            $day = $jikan->response[$this->day];
            $jikan->response = [];
            $jikan->response[$this->day] = $day;
        }

        $this->cache = json_encode($jikan->response);
        if ($this->cache !== false) {
            if (app('redis')->set($this->hash, $this->cache)) {
                app('redis')->expire($this->hash, CACHE_EXPIRE);
            }
        }

        return response()->json(
            $this->response + $jikan->response
        );
    }

}
