<?php

namespace App\Http\Middleware;

use Closure;

class Blacklist
{

    private $request;
    private $blacklist = [];

    public function handle($request, Closure $next)
    {
        $this->loadList();

        if ($this->inList()) {
            return response()->json([
                'error' => 'This IP has been blacklisted'
            ]);
        }

        return $next($request);
    }

    private function loadList() {
        if (!file_exists(BLACKLIST_PATH)) {
            file_put_contents(BLACKLIST_PATH, json_encode([]));
        }

        $this->blacklist = json_decode(file_get_contents(BLACKLIST_PATH), true);
    }

    private function inList() {
        $ip = $_SERVER['REMOTE_ADDR'];
        return in_array($ip, $this->blacklist) ? true : false;
    }

}
