# Resilience and cache headers

This page documents how jikan-rest behaves when MyAnimeList is slow, rate-limits
or fails, and which response headers tell a client that the payload it received
is cached or stale.

**The v4 JSON body is unchanged.** Everything here is signalled through HTTP
headers; there are no `is_cached` / `retry_after_seconds` body fields.

## Upstream circuit breaker

`app/Support/SourceCircuitBreaker.php` is a cache-backed breaker around MAL
scrapes.

| State | Meaning | Behaviour |
|---|---|---|
| Closed | Upstream looks healthy | Requests scrape normally |
| Open | `failure_threshold` failures inside `failure_window_seconds` | No scrape is attempted for `cooldown_seconds` |
| Half-open (probe) | Cooldown elapsed | The next request is allowed through; success closes the breaker, failure re-opens it |

Failures counted: HTTP `429`, `500`, `502`, `503`, `504` from the parser (and
bad responses surfaced as `JikanBadResponseException`). A `404` is **not** a
failure — it is negative-cached via `SCRAPE_MISS_TTL`.

| Variable | Default | Purpose |
|---|---:|---|
| `SOURCE_CIRCUIT_FAILURE_THRESHOLD` | `3` | Failures before the breaker opens |
| `SOURCE_CIRCUIT_COOLDOWN_SECONDS` | `60` | How long the breaker stays open |
| `SOURCE_CIRCUIT_FAILURE_WINDOW_SECONDS` | `300` | Window the failure counter is kept for |

When the breaker is open:

- with a cached copy → the cached copy is served as a fallback (headers below);
- with no cached copy → `503` (`MyAnimeList is temporarily unavailable.`).

**Shared-cache caveat.** Breaker state lives in the Laravel cache. With
`CACHE_DRIVER=redis` (or another shared store) the state is cluster-wide; with
the default `file` store it is **per worker**, so each Octane worker opens its
own breaker. Use a shared store in production if you want one global breaker.

## Stale fallback

A stale copy is served, instead of an error, when:

- the breaker is open, or
- the upstream returns `405`, `429` or a `5xx` **and** an expired cached copy
  exists for the same id.

Fallback responses carry:

| Header | Value | Meaning |
|---|---|---|
| `X-Jikan-Cached` | `1` | The payload was served from cache, not scraped now |
| `Retry-After` | seconds | Remaining breaker cooldown (`0` when it applies to a one-off upstream error) |

Clients should treat `X-Jikan-Cached: 1` as "may be stale" and may use
`Retry-After` to back off.

## Always-on cache headers

`app/Http/Middleware/ConditionalCache.php` and the `ResponseJikanCacheFlags`
macro add, on successful `GET` responses:

| Header | Meaning |
|---|---|
| `ETag` | Weak validator: `W/"<sha256 of body>"` |
| `Cache-Control` | `public, max-age=<endpoint TTL>` when the route has a TTL in `config/jikan.php` → `per_endpoint_cache_ttl` |
| `X-Request-Fingerprint` | Internal cache key for the response |
| `Expires`, `Last-Modified` | Cache envelope timestamps |

`If-None-Match` is honoured: a matching validator returns `304 Not Modified`
with no body. `If-None-Match: *` also matches.

These headers are additive; existing clients that ignore headers see the same
bodies and status codes as before.

## Verifying

```bash
# Breaker + stale fallback units
php vendor/bin/phpunit --filter='SourceCircuitBreakerTest|ResponseJikanCacheFlagsTest'

# ETag / 304 behaviour
php vendor/bin/phpunit --filter='ConditionalCache'

# Manual: read the validator, then replay it
curl -sSI http://localhost:8000/v4/anime/1 | grep -iE 'etag|cache-control'
curl -sS -o /dev/null -w '%{http_code}\n' \
  -H 'If-None-Match: <etag-from-above>' http://localhost:8000/v4/anime/1
```
