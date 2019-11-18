<?php

namespace App\Http\Middleware;

use App\Http\HttpHelper;
use Closure;
use Illuminate\Support\Facades\Cache;

class EtagMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->header('auth') === env('APP_KEY')) {
            return $next($request);
        }

        if (empty($request->segments())) {
            return $next($request);
        }

        if (!isset($request->segments()[1])) {
            return $next($request);
        }

        if (\in_array('meta', $request->segments())) {
            return $next($request);
        }

        $fingerprint = HttpHelper::resolveRequestFingerprint($request);
        if (
            $request->hasHeader('If-None-Match')
            && Cache::has($fingerprint)
            && md5(Cache::get($fingerprint)) === $request->header('If-None-Match')
        ) {
                return response('', 304);
        }

        return $next($request);
    }
}
