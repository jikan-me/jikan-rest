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

    private bool $microCachingEnabled;

    private Collection $config;

    public function __construct(array $config)
    {
        $config = collect($config);
        $this->perEndpointCacheTtl = $config->get("per_endpoint_cache_ttl", []);
        $this->defaultCacheExpire = $config->get("default_cache_expire", 0);
        $this->microCachingEnabled = in_array($config->get("micro_caching_enabled", false), [true, 1, "1", "true"]);
        $this->config = $config;
    }

    public function cacheTtlForEndpoint(string $endpoint): ?int
    {
        return collect($this->perEndpointCacheTtl)->get($endpoint);
    }

    public function defaultCacheExpire(): int
    {
        return $this->defaultCacheExpire;
    }

    public function isMicroCachingEnabled(): bool
    {
        return $this->microCachingEnabled;
    }

    public function maxResultsPerPage(?int $defaultValue = null): int
    {
        return $this->config->get("max_results_per_page", $defaultValue ?? 25);
    }
}
