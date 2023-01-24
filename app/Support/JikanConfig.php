<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Jikan behavior config
 */
final class JikanConfig
{
    /**
     * @var array<string, int> $perEndpointCacheTtl
     */
    private array $perEndpointCacheTtl;

    private int $defaultCacheExpire;

    public function __construct(array $config)
    {
        $config = collect($config);
        $this->perEndpointCacheTtl = $config->get("per_endpoint_cache_ttl", []);
        $this->defaultCacheExpire = $config->get("default_cache_expire", 0);
    }

    public function cacheTtlForEndpoint(string $endpoint): ?int
    {
        return collect($this->perEndpointCacheTtl)->get($endpoint);
    }

    public function defaultCacheExpire(): int
    {
        return $this->defaultCacheExpire;
    }
}
