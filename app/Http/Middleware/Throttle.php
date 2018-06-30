<?php

namespace App\Http\Middleware;

use Closure;

class Throttle
{

    private $ip;
    private $hit;

    private $sessions = [];
    private $rateLimited = false;

    private $request_hash;
    private $request_cached;

    public function handle($request, Closure $next)
    {

        $response = $next($request);

        $this->loadSessions(); // load the session

        if (!isset($response->original['error'])) { // don't throttle errors

            $this->request_hash = $response->original['request_hash'];
            $this->request_cached = $response->original['request_cached'];

            if (!$this->request_cached) { // don't throttle cached requests
                $this->hit(); // increase API Call Count
            }
        }

        $this->rateLimit(); // check if it's over the limit

        $this->save(); // save the updated session

        if ($this->rateLimited) {
            return response()->json(['error' => 'You have reached your limit of ' . RATE_LIMIT . ' API calls per day, please try again later'], 429);
        }

        return $response;
    }

    public function hit() {
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $date = date("d-m-Y");

        if (!isset($this->sessions[$this->ip])) { // register the session
            $this->sessions[$this->ip] = [
                $date => 0
            ];
        }

        if (!isset($this->sessions[$this->ip][$date])) { // register today's session
            $this->sessions[$this->ip][$date] = 0;
        }

        $this->sessions[$this->ip][$date]++; // increase API Call count

        $this->hit = $this->sessions[$this->ip][$date];
    }

    public function rateLimit() {
        $this->rateLimited = ($this->hit > RATE_LIMIT) ? true : false;
    }

    public function loadSessions() {
        $this->sessions = file_exists(SESSION_STORAGE_PATH) ? json_decode(file_get_contents(SESSION_STORAGE_PATH), true) : file_put_contents(SESSION_STORAGE_PATH, json_encode([]));
    }

    public function save() {
        file_put_contents(SESSION_STORAGE_PATH, json_encode($this->sessions));
    }
}