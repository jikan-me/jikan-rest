<?php

namespace App\Concerns;

trait ScraperCacheTtl
{
    protected function cacheTtl(): int
    {
        return (int) env('CACHE_DEFAULT_EXPIRE');
    }
}
