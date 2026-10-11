<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class Insights
 * @package App\Http\Middleware
 */
class Insights
{

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    /**
     * @param Request $request
     * @param $response
     * @return void
     */
    public function terminate(Request $request, $response)
    {
        if (! config('jikan.insights_enabled')) {
            return;
        }

        if (isset($response->original['error'])) {
            return;
        }

        // @todo scaling: implement as scheduled event if needed
        // Delete requests older than INSIGHTS_MAX_STORE
        DB::table('insights')
            ->where('timestamp', '<', time() - config('jikan.insights_max_store_time') )
            ->delete();

        DB::table('insights')
            ->insert([
                'timestamp' => time(),
                'url' => $request->getRequestUri(),
                'type' => ""
            ]);
    }

}
