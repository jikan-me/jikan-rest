<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\Handler as Handler;
use Jikan\Jikan;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;

class MangaController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public $id;
    public $extend;
    public $extendArgs;

    private $validExtends = ['characters', 'news', 'pictures', 'stats', 'forum', 'moreinfo'];

    public function request($id, $extend = null, $extendArgs = null) {

        $this->id = $id;
        $this->extend = $extend;
        $this->extendArgs = $extendArgs;

        $this->hash = sha1('manga' . $this->id . $this->extend . $this->extendArgs);
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
                case 'characters':
                    try {

                        $jikan->Manga($this->id, [CHARACTERS]);

                    } catch (\Exception $e) {

                        return response()->json(
                            ['error' => $e->getMessage()], 404
                        );
                    }
                    break;
                case 'news':
                    try {

                        $jikan->Manga($this->id, [NEWS]);

                    } catch (\Exception $e) {

                        return response()->json(
                            ['error' => $e->getMessage()], 404
                        );
                    }
                    break;
                case 'pictures':
                    try {

                        $jikan->Manga($this->id, [PICTURES]);

                    } catch (\Exception $e) {

                        return response()->json(
                            ['error' => $e->getMessage()], 404
                        );
                    }
                    break;

                case 'stats':
                    try {

                        $jikan->Manga($this->id, [STATS]);

                    } catch (\Exception $e) {

                        return response()->json(
                            ['error' => $e->getMessage()], 404
                        );
                    }
                    break;

                case 'moreinfo':
                    try {

                        $jikan->Manga($this->id, [MORE_INFO]);

                    } catch (\Exception $e) {

                        return response()->json(
                            ['error' => $e->getMessage()], 404
                        );
                    }
                    break;

                case 'forum':
                    try {

                        $jikan->Manga($this->id, [FORUM]);

                    } catch (\Exception $e) {

                        return response()->json(
                            ['error' => $e->getMessage()], 404
                        );
                    }
                    break;
            }

        } else {
            try {

                $jikan->Manga($this->id);

            } catch (\Exception $e) {
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
