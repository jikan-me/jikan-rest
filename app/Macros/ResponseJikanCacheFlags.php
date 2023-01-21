<?php

namespace App\Macros;

use App\Concerns\ScraperCacheTtl;
use App\Support\CachedData;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

/**
 * @mixin Response
 */
final class ResponseJikanCacheFlags
{
    use ScraperCacheTtl;

    public function __invoke(): \Closure
    {
        return function (string $cacheKey, CachedData $scraperResults) {
            /**
             * @var Response $this
             */
            return $this
                ->header("X-Request-Fingerprint", $cacheKey)
                ->setTtl(ResponseJikanCacheFlags::cacheTtl())
                ->setExpires(Carbon::createFromTimestamp($scraperResults->expiry()))
                ->setLastModified(Carbon::createFromTimestamp($scraperResults->lastModified()));
        };
    }
}
