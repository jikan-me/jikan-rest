<?php

namespace App\Concerns;

use Illuminate\Support\Env;

trait ScraperCacheTtl
{
    protected static function cacheTtl(): int
    {
        return (int) Env::get('CACHE_DEFAULT_EXPIRE');
    }
}
