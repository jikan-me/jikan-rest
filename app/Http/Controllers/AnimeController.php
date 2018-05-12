<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\Handler as Handler;
use Jikan\Jikan;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Lazer\Classes\Database as Lazer;

class AnimeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public $id;
    public $extend;
    public $extendArgs;

    private $validExtends = ['episodes', 'characters_staff', 'news', 'videos', 'pictures', 'stats', 'forum', 'moreinfo'];

    public function request($id, $extend = null, $extendArgs = null) {

        $this->id = $id;
        $this->extend = $extend;
        $this->extendArgs = is_array($extendArgs) ? $extendArgs[0] : $extendArgs;

        $this->hash = sha1('anime' . $this->id . $this->extend . $this->extendArgs);
        $this->response['request_hash'] = $this->hash;
        $this->response['request_cached'] = false;

        if (app('redis')->exists($this->hash)) {
            $this->response['request_cached'] = true;
            return response()->json(
                $this->response + json_decode(app('redis')->get($this->hash), true)
            );
        }

        $jikan = new Jikan;

        if (isset($this->extend)) {
            if (!in_array($this->extend, $this->validExtends)) {
                return response()->json(
                    ['error' => 'Invalid extended request: "' . $this->extend . '"'], 400
                );
            }

            switch ($this->extend) {
                case 'episodes':
                    try {

                        if (!isset($this->extendArgs) || empty($this->extendArgs)) {
                            $this->extendArgs = 1;
                        } else {
                            intval($this->extendArgs);
                        }

                        $this->extendArgs = $this->extendArgs <= 0 ? 1 : $this->extendArgs;

                        $jikan->Anime($this->id, [EPISODES => $this->extendArgs]);

                    } catch (\Exception $e) {
                        Bugsnag::notifyException($e);
                        return response()->json(
                            ['error' => $e->getMessage()], 404
                        );  
                    }
                    break;

                case 'characters_staff':
                    try {

                        $jikan->Anime($this->id, [CHARACTERS_STAFF]);

                    } catch (\Exception $e) {
                        Bugsnag::notifyException($e);
                        return response()->json(
                            ['error' => $e->getMessage()], 404
                        );
                    }
                    break;

                case 'news':
                    try {

                        $jikan->Anime($this->id, [NEWS]);

                    } catch (\Exception $e) {
                        Bugsnag::notifyException($e);
                        return response()->json(
                            ['error' => $e->getMessage()], 404
                        );
                    }
                    break;

                case 'videos':
                    try {

                        $jikan->Anime($this->id, [VIDEOS]);

                    } catch (\Exception $e) {
                        Bugsnag::notifyException($e);
                        return response()->json(
                            ['error' => $e->getMessage()], 404
                        );
                    }
                    break;

                case 'pictures':
                    try {

                        $jikan->Anime($this->id, [PICTURES]);

                    } catch (\Exception $e) {
                        Bugsnag::notifyException($e);
                        return response()->json(
                            ['error' => $e->getMessage()], 404
                        );
                    }
                    break;

                case 'stats':
                    try {

                        $jikan->Anime($this->id, [STATS]);

                    } catch (\Exception $e) {
                        Bugsnag::notifyException($e);
                        return response()->json(
                            ['error' => $e->getMessage()], 404
                        );
                    }
                    break;

                case 'moreinfo':
                    try {

                        $jikan->Anime($this->id, [MORE_INFO]);

                    } catch (\Exception $e) {
                        Bugsnag::notifyException($e);
                        return response()->json(
                            ['error' => $e->getMessage()], 404
                        );
                    }
                    break;

                case 'forum':
                    try {

                        $jikan->Anime($this->id, [FORUM]);

                    } catch (\Exception $e) {
                        Bugsnag::notifyException($e);
                        return response()->json(
                            ['error' => $e->getMessage()], 404
                        );
                    }
                    break;
            }

        } else {
            try {

                $jikan->Anime($this->id);

            } catch (\Exception $e) {
                Bugsnag::notifyException($e);
                return response()->json(
                    ['error' => $e->getMessage()], 404
                );
            }
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
