<?php

namespace App\Support;

use App\Concerns\ScraperCacheTtl;
use App\JikanApiModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Env;
use MongoDB\BSON\UTCDateTime;

final class CachedData
{
    private int $cacheTimeToLive;

    private function __construct(
        private readonly Collection $scraperResult,
        int $cacheTtl
    )
    {
        $this->cacheTimeToLive = $cacheTtl;
    }

    public static function from(Collection $scraperResult): self
    {
        return new self($scraperResult, app(CacheOptions::class)->ttl());
    }

    public static function fromArray(array $scraperResult): self
    {
        return self::from(collect($scraperResult));
    }

    public static function fromModel(JikanApiModel $model): self
    {
        return self::fromArray($model->toArray());
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
        $ttl = $this->cacheTimeToLive;
        return $modifiedAt !== null ? $ttl + $modifiedAt : $ttl;
    }

    public function cacheTtl(): int
    {
        return $this->cacheTimeToLive;
    }

    public function lastModified(): ?int
    {
        if ($this->scraperResult->isEmpty()) {
            return null;
        }

        $result = $this->scraperResult;

        if (null !== $modifiedAt = $result->get("modifiedAt")) {
            return $this->mixedToTimestamp($modifiedAt);
        }

        return null;
    }

    private function mixedToTimestamp(mixed $modifiedAt): ?int
    {
        if ($modifiedAt instanceof UTCDateTime) {
            return (int) $modifiedAt->toDateTime()->format("U");
        }
        if ($modifiedAt instanceof \DateTimeInterface) {
            return (int) $modifiedAt->format("U");
        }
        if (is_string($modifiedAt)) {
            return Carbon::createFromTimeString($modifiedAt)->format("U");
        }

        return null;
    }
}
