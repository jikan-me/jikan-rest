<?php

namespace App\Support;

use App\Concerns\ScraperCacheTtl;
use App\JikanApiModel;
use Illuminate\Support\Collection;

final class CachedData
{
    use ScraperCacheTtl;

    public function __construct(
        private readonly Collection $scraperResult
    )
    {
    }

    public static function from(Collection $scraperResult): self
    {
        return new self($scraperResult);
    }

    public function collect(): Collection
    {
        return $this->scraperResult;
    }

    public function offsetGet(string $key): mixed
    {
        return $this->scraperResult->offsetGet($key);
    }

    public function offsetSet(string $key, mixed $value): void
    {
        $this->scraperResult->offsetSet($key, $value);
    }

    public function isEmpty(): bool
    {
        return $this->scraperResult->isEmpty();
    }

    public function isExpired(): bool
    {
        $lastModified = $this->lastModified();

        if ($lastModified === null) {
            return true;
        }

        $expiry = $this->expiry();

        return time() > $expiry;
    }

    public function toArray(): array
    {
        return $this->scraperResult->toArray();
    }

    public function expiry(): int
    {
        $modifiedAt = $this->lastModified();
        $ttl = $this->cacheTtl();
        return $modifiedAt !== null ? $ttl + $modifiedAt : $ttl;
    }

    public function lastModified(): ?int
    {
        if ($this->scraperResult->isEmpty()) {
            return null;
        }

        $result = $this->scraperResult->first();

        if ($result instanceof JikanApiModel && !is_null($result->getAttributeValue("modifiedAt"))) {
            return (int) $result["modifiedAt"]->toDateTime()->format("U");
        }

        if (is_array($result) && array_key_exists("modifiedAt", $result)) {
            return (int) $result["modifiedAt"]->toDateTime()->format("U");
        }

        if (is_object($result) && property_exists($result, "modifiedAt")) {
            return $result->modifiedAt->toDateTime()->format("U");
        }

        return null;
    }
}
