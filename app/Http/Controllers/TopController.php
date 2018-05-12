<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\Handler as Handler;
use Jikan\Jikan;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Lazer\Classes\Database as Lazer;

class TopController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public $type;
    public $subtype;
    public $page;

    private $validTypes = ['anime', 'manga'];
    private $validSubTypes = ['airing', 'upcoming', 'tv', 'movie', 'ova', 'special', 'manga', 'novels', 'oneshots', 'doujin', 'manhwa', 'manhua', 'bypopularity', 'favorite'];

    public function request($type = null, $page = 1, $subtype = null) {

        $antiXss = new \voku\helper\AntiXSS();

        $this->type = $antiXss->xss_clean($type);
        $this->page = (int) $page;

        if (!in_array($this->type, $this->validTypes)) {
            return response()->json(
                ['error' => 'Invalid type request: "' . $this->type . '/' . $this->page . '/' . $this->subtype . '"'], 400
            );
        }

        if (!is_null($subtype)) {
            $this->subtype = $antiXss->xss_clean($subtype);
            if (!in_array($this->subtype, $this->validSubTypes)) {
                return response()->json(
                    ['error' => 'Invalid sub type request: "' . $this->type . '/' . $this->page . '/' . $this->subtype . '"'], 400
                );
            }
        }

        $this->hash = sha1('top' . $this->type . $this->page . $this->subtype);
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
            
            $jikan->Top($this->type, $this->page, $this->subtype);
            
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
                app('redis')->expire($this->hash, CACHE_EXPIRE_SEARCH);
            }
        }

        return response()->json(
            $this->response + $jikan->response
        );
    }

}
