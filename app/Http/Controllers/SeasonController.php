<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\Handler as Handler;
use Jikan\Jikan;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Lazer\Classes\Database as Lazer;

class SeasonController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public $year;
    public $season;
    
    private $validSeasons = ['summer', 'spring', 'fall', 'winter'];

    public function request($year = null, $season = null) {

        $antiXss = new \voku\helper\AntiXSS();

        if (!is_null($year) && !is_null($season)) {
            $this->year = (int) $year;

            $this->season = $antiXss->xss_clean($season);
            if (!in_array($this->season, $this->validSeasons)) {
                return response()->json(
                    ['error' => 'Invalid season request: "' . $this->year . '/' . $this->season . '"'], 400
                );
            }
        }

        $this->hash = sha1('season' . $this->year . $this->season);
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

            if (!is_null($this->year) && !is_null($this->season)) {
                $jikan->Seasonal($this->season, $this->year);
            } else {
                $jikan->Seasonal();
            }
            
        } catch (\Exception $e) {
            Bugsnag::notifyException($e);
            return response()->json(
                ['error' => $e->getMessage()], 404
            );
        }

        if (empty($jikan->response) || $jikan->response === false) {
            return response()->json(['error' => 'MyAnimeList Rate Limiting reached. Slow down!'], 429);
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
