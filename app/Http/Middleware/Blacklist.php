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

        if (\in_array($_SERVER['REMOTE_ADDR'], $this->blacklist)) {
            return response()
                ->json([
                    'status' => 403,
                    'type' => null,
                    'message' => 'You have been blocked from the service for breaching Terms of Use',
                    'error' => null
                ], 403);
        }

        return $next($request);
    }

    private function loadList()
    {
        if (!file_exists(BLACKLIST_PATH)) {
            file_put_contents(BLACKLIST_PATH, json_encode([]));
        }

        $this->blacklist = json_decode(file_get_contents(BLACKLIST_PATH), true);
    }
}
