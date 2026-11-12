<?php

namespace App\Macros;

use App\Support\CachedData;
use App\Support\CacheOptions;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

/**
 * @mixin Response
 */
final class ResponseJikanCacheFlags
{
    public function __invoke(): \Closure
    {
        return function (string $cacheKey, CachedData $scraperResults) {
            /**
             * @var Response $this
             */
            $this
                ->header('X-Request-Fingerprint', $cacheKey)
                ->setTtl(app(CacheOptions::class)->ttl())
                ->setExpires(Carbon::createFromTimestamp($scraperResults->expiry()))
                ->setLastModified(Carbon::createFromTimestamp($scraperResults->lastModified() ?? 0));

            if ($scraperResults->isFallback()) {
                $this->header('X-Jikan-Cached', '1');
                if ($scraperResults->fallbackRetryAfter() > 0) {
                    $this->header('Retry-After', (string) $scraperResults->fallbackRetryAfter());
                }
            }

            return $this;
        };
    }
}
