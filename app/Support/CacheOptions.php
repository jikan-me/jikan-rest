<?php

namespace App\Support;

final class CacheOptions
{
    private ?int $ttl = null;

    public function __construct(private readonly JikanConfig $jikanConfig)
    {
    }

    public function ttl(): int
    {
        return $this->ttl ?? $this->jikanConfig->defaultCacheExpire();
    }

    public function setTtl(?int $ttl): void
    {
        $this->ttl = $ttl;
    }
}
