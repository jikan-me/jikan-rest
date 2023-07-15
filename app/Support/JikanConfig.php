<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Arr;

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

    private int $textMatchBuckets;

    private int $typoTokensThreshold;

    private int $dropTokensThreshold;

    private int $searchCutOffMs;

    private string $exhaustiveSearch;

    public function __construct(array $config)
    {
        $this->perEndpointCacheTtl = Arr::get($config, "per_endpoint_cache_ttl", []);
        $this->defaultCacheExpire = Arr::get($config, "default_cache_expire", 0);
        $this->microCachingEnabled = in_array(Arr::get($config, "micro_caching_enabled", false), [true, 1, "1", "true"]);
        $this->textMatchBuckets = Arr::get($config,"typesense_options.text_match_buckets", 1);
        $this->exhaustiveSearch = (string) Arr::get($config, "typesense_options.exhaustive_search", "false");
        $this->config = collect($config);
        $this->typoTokensThreshold = Arr::get($config, "typesense_options.typo_tokens_threshold") ?? $this->maxResultsPerPage();
        $this->dropTokensThreshold = Arr::get($config, "typesense_options.drop_tokens_threshold") ?? $this->maxResultsPerPage();
        $this->searchCutOffMs = Arr::get($config, "typesense_options.search_cutoff_ms", 450);
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
        return (int) $this->config->get("max_results_per_page", $defaultValue ?? 25);
    }

    public function textMatchBuckets(): int
    {
        return $this->textMatchBuckets;
    }

    public function typoTokensThreshold(): int
    {
        return $this->typoTokensThreshold;
    }

    public function dropTokensThreshold(): int
    {
        return $this->dropTokensThreshold;
    }

    public function exhaustiveSearch(): string
    {
        $normalizedValue = strtolower($this->exhaustiveSearch);
        return match($this->exhaustiveSearch) {
            "0", "FALSE" => "false",
            "1", "TRUE" => "true",
            default => in_array($normalizedValue, ["true", "false"]) ? $normalizedValue : "false"
        };
    }

    public function searchCutOffMs(): int
    {
        return $this->searchCutOffMs;
    }
}
