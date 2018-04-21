<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\Handler as Handler;
use Jikan\Jikan;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;

class CharacterController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public $id;
    public $extend;
    public $extendArgs;

    private $validExtends = ['pictures'];

    public function request($id, $extend = null, $extendArgs = null) {

        $this->id = $id;
        $this->extend = $extend;
        $this->extendArgs = $extendArgs;

        $this->hash = sha1('character' . $this->id . $this->extend . $this->extendArgs);
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
                case 'pictures':

                    try {

                        $jikan->Character($this->id, [PICTURES]);

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

                $jikan->Character($this->id);

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
