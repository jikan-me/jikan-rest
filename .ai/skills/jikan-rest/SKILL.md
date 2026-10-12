---
name: jikan-rest
description: How the jikan-rest API (Laravel 13 on Octane/RoadRunner, MongoDB, read-only MyAnimeList v4 JSON API) is structured and how to change it safely - request flow, adding an endpoint, the v4 contract rule, Octane state rules, route and cache-TTL naming, and the gate commands. Use for any change under app/, routes/, config/jikan.php or tests/.
metadata:
  origin: jikan-rest
---

# jikan-rest

A **read-only public JSON API** over MyAnimeList data: no auth, sessions, CSRF, Blade or Livewire. Laravel 13
(PHP 8.3+, runtime 8.4), Octane/RoadRunner, MongoDB (`mongodb/laravel-mongodb`), the `jikan-me/jikan` parser,
optional Scout/Typesense search. The generic Laravel skills in this folder apply except where they assume auth.

## Request flow

`routes/web.v4.php` (class-based, **named** routes) -> `app/Http/Controllers/V4DB` controller -> a DTO in
`app/Dto` (spatie Data; its fields are the cache fingerprint) -> `DefaultMediator` (`app/Support`) -> a handler in
`app/Features` -> repositories (`app/Repositories`, built on `RepositoryQueryBase`) or the cached scraper service
-> a resource in `app/Http/Resources/V4`. Details: `docs/ARCHITECTURE.md`.

## Rules that are easy to break

1. **The v4 JSON contract is frozen** for default requests. Only additive, opt-in parameters are allowed
   (see `CLAUDE.md`). Error bodies keep `{status, type, message, error}`.
2. **Octane keeps the process alive between requests.** Never resolve through `$this->app` inside a provider
   closure; use the container the closure receives, or `scoped()` bindings. Repositories hand out cloned query
   builders. Per-request state belongs in `config/octane.php` `flush`.
3. **No `env()` outside `config/`** (and the pre-config closures in `bootstrap/app.php`). The container runs
   `config:cache`, after which `env()` returns null.
4. **Routes:** name every controller route `v4.<resource>.<action>`. The name keys
   `per_endpoint_cache_ttl` in `config/jikan.php`. Constrain `{id}` with `'where' => ['id' => '[0-9]+']` in the
   group attributes; chaining `->where()` after `Route::group()` does nothing.
5. **Enums are docblock-first** (`@method static self value()`), keep those lines.
6. **Rate limiting is opt-in** (`RATE_LIMIT_PER_MINUTE`, default 0) and needs `TRUSTED_PROXIES` plus a shared
   cache. CORS is opt-in (`CORS_MIDDLEWARE`).

## Adding an endpoint

Follow `docs/RECIPE_ADD_ENDPOINT.md`, in order: route -> DTO -> handler (bound in the factory table behind
`AppServiceProvider::registerRequestHandlers()`) -> service/repository -> resource -> controller with OpenAPI
attributes -> tests -> regenerate the generated artifacts -> TTL entry.

## Tests

Tests extend `Tests\TestCase` and need MongoDB. They run against `jikan_tests` (the trait truncates ~45
collections and throws if the connection is not the test database). Upstream HTTP to MAL is mocked to throw. Use
`getJsonWithParams()` for query-string endpoints and call `assertStatus()` on the returned response.

## Gates (all must stay green)

```bash
php vendor/bin/phpunit --no-coverage
php -d error_reporting=24575 -d memory_limit=-1 vendor/bin/phpstan analyse --no-progress
php artisan l5-swagger:generate && composer endpoints:write   # then `composer endpoints:check`
php artisan route:list --path=v4                              # 110 routes
```

Known traps are recorded in `.backend-suite/known-traps.json`; operator-visible changes in `docs/UPGRADING.md`.
