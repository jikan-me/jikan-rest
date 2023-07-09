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

    private int $textMatchBuckets;

    private int $typoTokensThreshold;

    private int $dropTokensThreshold;

    private int $searchCutOffMs;

    private string $exhaustiveSearch;

    public function __construct(array $config)
    {
        $config = collect($config);
        $this->perEndpointCacheTtl = $config->get("per_endpoint_cache_ttl", []);
        $this->defaultCacheExpire = $config->get("default_cache_expire", 0);
        $this->microCachingEnabled = in_array($config->get("micro_caching_enabled", false), [true, 1, "1", "true"]);
        $this->textMatchBuckets = $config->get("typesense_options.text_match_buckets", 85);
        $this->exhaustiveSearch = (string) $config->get("typesense_options.exhaustive_search", "false");
        $this->config = $config;
        $this->typoTokensThreshold = $config->get("typesense_options.typo_tokens_threshold", $this->maxResultsPerPage());
        $this->dropTokensThreshold = $config->get("typesense_options.drop_tokens_threshold", $this->maxResultsPerPage());
        $this->searchCutOffMs = $config->get("typesense_options.search_cutoff_ms", 450);
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
