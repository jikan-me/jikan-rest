<?php

namespace App\Support;

use App\JikanApiModel;
use ArrayAccess;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Macroable;
use MongoDB\BSON\UTCDateTime;

/**
 * A class to represent cached scraper results. This is just a fancy collection which knows about
 * cache ttl and whether the data is expired in the cache or not.
 */
final class CachedData implements ArrayAccess
{
    use ForwardsCalls, Macroable {
        __call as macroCall;
    }

    private int $cacheTimeToLive;

    private function __construct(
        private readonly Collection $scraperResult,
        int $cacheTtl
    )
    {
        $this->cacheTimeToLive = $cacheTtl;
    }

    /**
     * @param Collection $scraperResult Always a collection which has the result from the scraper wrapped under a key
     *                                  or a document from the db.
     * @return static
     */
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

        if (null !== $modifiedAt = $result->get("updated_at")) {
            return $this->mixedToTimestamp($modifiedAt);
        }

        return null;
    }

    /**
     * Dynamically get elements by key from the underlying scraper result collection.
     * Additionally, this will add support for DelegatesToResource trait which is used by JsonResource class,
     * making the instances of this class passable as ctor arg for laravel resources.
     * @param string|int $key
     * @return \Closure|null
     */
    public function __get(string|int $key)
    {
        return $this->scraperResult->get($key);
    }

    /**
     * Determine if an element with the specified key exists in the underlying scraper result collection.
     * Additionally, this will add support for DelegatesToResource trait which is used by JsonResource class,
     * making the instance of this class passable as ctor arg for laravel resources.
     * @param string|int $key
     * @return bool
     */
    public function __isset(string|int $key)
    {
        return $this->scraperResult->has($key);
    }

    /**
     * Dynamically pass method calls to the underlying scraper result collection.
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (self::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return $this->forwardCallTo($this->scraperResult, $method, $parameters);
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

    public function offsetExists(mixed $offset): bool
    {
        return $this->scraperResult->offsetExists($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->scraperResult->offsetGet($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        // noop, readonly
    }

    public function offsetUnset(mixed $offset): void
    {
        // noop, readonly
    }
}
