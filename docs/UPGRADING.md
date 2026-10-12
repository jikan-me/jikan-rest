# Upgrading from the Lumen 9 release

This release moves jikan-rest from Lumen 9 to Laravel 13 (PHP 8.3+, Octane/RoadRunner).
The public **v4 JSON contract is unchanged** for valid requests. This page lists everything
an operator needs to know that is *not* a contract change. Each section notes what it was
on the Lumen release ("before") and what it is now.

## New and changed environment variables

| Variable | Before | Now |
|---|---|---|
| `REPORTING`, `REPORTING_DRIVER` | `REPORTING=true` + `REPORTING_DRIVER=sentry` registered Sentry | **Same contract.** Sentry is registered only when both are set; it is excluded from package auto-discovery. Default `false`. |
| `SENTRY_LARAVEL_DSN`, `SENTRY_TRACES_SAMPLE_RATE` | placeholder DSN, `0.5` | empty DSN, `0.1` |
| `MICROCACHING_EXPIRE` | default `60` | default `60` (an interim revision of this branch had changed it to `5`) |
| `RATE_LIMIT_PER_MINUTE` | did not exist (no throttling) | **New, default `0` = disabled.** Per-IP requests per minute for `/v4`. Enable only with a shared cache (`CACHE_DRIVER=redis`; the `array` store is per worker) and `TRUSTED_PROXIES`. |
| `TRUSTED_PROXIES` | did not exist | **New.** Comma-separated IPs/CIDRs of your reverse proxy / load balancer, or `*`. Needed so `request()->ip()` is the real client. Empty = trust nothing. |
| `QUEUE_WORKERS` | RoadRunner ran one `laravel_queue_worker_1` process (`JIKAN_QUEUE_WORKER_PROCESS_NUM`) | **New, default `0`.** The container entrypoint starts this many `queue:work` processes. Set it if you use `SCOUT_QUEUE=true` or dispatch jobs. |
| `CACHE_MAGAZINES_EXPIRE` | documented in `.env.dist` | The endpoint TTL map reads **`CACHE_MAGAZINE_EXPIRE`** (singular). The plural name was only consumed by the cache-refresh jobs, which were removed. |
| `CACHING`, `DB_CACHING`, `CACHE_META_EXPIRE`, `CACHE_PRODUCERS_EXPIRE`, `CACHE_404_EXPIRE`, `CACHE_MICROCACHE_EXPIRE`, `SOURCE`, `SOURCE_BAD_HEALTH_THRESHOLD`, `SWAGGER_VERSION` | in `.env.dist` | No longer read by the application (several were only consumed by the removed cache-refresh jobs); remove them from your environment. |
| `INSIGHTS`, `INSIGHTS_MAX_STORE_TIME` | enabled the unfinished insights feature | Removed together with the feature (see below). |

## Runtime and operations

| Topic | Before (Lumen + `lumen-roadrunner`) | Now (Laravel + Octane/RoadRunner) |
|---|---|---|
| Worker pool | `.rr.yaml`, tuned through `RR_MAX_WORKER_MEMORY`, `RR_MAX_REQUEST_SIZE_MB`, `JIKAN_QUEUE_WORKER_PROCESS_NUM` | `OCTANE_WORKERS` (default `auto` = one per CPU) and `OCTANE_MAX_REQUESTS` (default `500`, workers are recycled after this many requests). `.rr.yaml` is no longer shipped. |
| RoadRunner memory supervisor / max request size | `RR_MAX_WORKER_MEMORY` (128), `RR_MAX_REQUEST_SIZE_MB` (256) | **Not mapped.** Octane generates its own RoadRunner config. Use `OCTANE_MAX_REQUESTS` to bound worker memory growth, or supply your own RoadRunner v3 config with `OCTANE_RR_CONFIG=/path/to/.rr.yaml`. |
| Queue workers | `laravel_queue_worker_1` RoadRunner service | `QUEUE_WORKERS=N` (see above). |
| `APP_KEY` | not needed by Lumen | The container entrypoint generates one on first boot when none is supplied. Set `APP_KEY` (or `APP_KEY__FILE`) explicitly in production so it is stable across restarts. |
| Scheduler | all indexers `->daily()` at 00:00 | Staggered `dailyAt()` between 00:10 and 01:30, with `withoutOverlapping()->onOneServer()`. `onOneServer` needs a cache store shared by all instances (`CACHE_DRIVER=redis`); with the per-process `array` store it is a no-op. |

## Behaviour changes

- **Errors.** With `APP_DEBUG=false` the `error` and `trace` fields of error responses are now
  empty strings, and the prefilled GitHub `report_url` no longer embeds the exception message
  or file path. Status codes and `type`/`message` are unchanged.
- **Non-numeric ids** (`/v4/anime/abc`, `/v4/manga/xyz`, characters, people, producers) are a
  `404`, as on the Lumen release.
- **Per-endpoint cache TTL keys** in `config/jikan.php` (`per_endpoint_cache_ttl`) are now route names
  (`v4.anime.main`) instead of `AnimeController@main`. Only matters if you override that map.
- **CORS** is opt-in again via `CORS_MIDDLEWARE=true` (default `false`, as before). The app uses
  Laravel's built-in `HandleCors` (all origins, `GET`/`OPTIONS`); the custom `CorsMiddleware` is gone.
- **Cache bypass removed.** The `auth: <APP_KEY>` request header that skipped the micro-cache
  existed only for the cache-refresh jobs (`UpdateCacheJob`, `UpdateDatabaseJob`), which were
  deleted. Nothing replaces it; `CACHE_BYPASS_TOKEN` is no longer read.
- **Removed endpoints.** `/v4/insights` and `/v4/insights/trends` (work in progress, gated off
  by `INSIGHTS=false`) and the `Insights` middleware, controller and resources were removed.
- **New endpoints.** `GET /up` (liveness), `GET /llms.txt`, `GET /v4/ai/manifest.json`,
  `GET /v4/docs` (Scalar API reference), `GET /v4/docs/openapi` (OpenAPI JSON). `/v4/api-docs`
  now 301-redirects to `/v4/docs/openapi`, and `/docs` and `/v4/documentation` to `/v4/docs`.
- **Operational setup.** Dependencies are resolved against PHP 8.4 and `ext-mongodb` ≥ 2.5.
  A fresh clone needs `composer install`, which relies on the tracked
  `bootstrap/cache/.gitignore` placeholder.
