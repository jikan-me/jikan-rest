# Upgrading

## 4.2.x → 4.3.0 for self-hosters (platform migration)

4.3.0 keeps the **same `/v4` API contract** (routes, query parameters and response keys are
unchanged) on a **new runtime**: Lumen 9 is replaced by Laravel 13 on Octane/Swoole. For
API consumers it is a minor release; for operators it is a platform migration, not a drop-in
upgrade. Plan a fresh image/environment rather than an in-place `composer update`.

### Requirements

| Component | 4.2.x (Lumen) | 4.3.0 |
|---|---|---|
| PHP | 8.1 | **≥ 8.4.4** (`composer.json` `php: ^8.4.4`; 8.4.0–8.4.3 are refused by the parser, php-src GH-17481) |
| MongoDB PHP extension | 1.x | **`ext-mongodb` 2.x, ≥ 2.5** (required by `mongodb/mongodb` 2.5 / `mongodb/laravel-mongodb` ^5) |
| Framework | Lumen 9 | Laravel 13 (`laravel/framework` ^13.0) |
| HTTP server | `lumen-roadrunner`, RoadRunner 2.12.3 | Laravel Octane ^2 on **Swoole** (`ext-swoole` 6.x, built into the image); `ext-pcntl` is required (the image also installs `ext-sockets` and `ext-brotli`). RoadRunner is no longer supported. |
| MongoDB server | `mongo:focal` in compose | **`mongo:8.0`** (`docker-compose.yml`; the supported target). CI temporarily runs `mongo:7.0` because 8.0.34 and earlier refuse to start on GitHub runners' kernels ([SERVER-121912](https://jira.mongodb.org/browse/SERVER-121912)); it moves back to 8.0 once 8.0.35 is published |
| Typesense (optional) | 0.24.1 | 27.0 (`docker-compose.typesense.yml`, `typesense/typesense-php` ^6); search is off by default (`SCOUT_DRIVER=null`) |
| Parser | jikan-me/jikan 4.x | jikan-me/jikan v5.0.0 (^5.0) |

### What changed for operators

- **MongoDB server upgrade.** 4.2.x's compose used `mongo:focal` (MongoDB 4.x/5.x era); 4.3.0
  targets **MongoDB 8.0**. MongoDB upgrades one major version at a time: back up first, then
  step through each release (e.g. 5.0 → 6.0 → 7.0 → 8.0), setting `featureCompatibilityVersion`
  to the new version after each step. See MongoDB's
  [standalone upgrade guide](https://www.mongodb.com/docs/manual/release-notes/8.0-upgrade-standalone/).
  On Linux kernel 6.19+ hosts see [TROUBLESHOOTING.md](TROUBLESHOOTING.md#mongodb-exits-at-startup-linux-kernel-versions-619-and-newer).

- **Environment variables.** Start from the new `.env.dist`; the full before/after table is in
  [New and changed environment variables](#new-and-changed-environment-variables). Highlights:
  - `CACHE_DRIVER` now defaults to **`file`** (was `array`). `CACHE_STORE` (the Laravel name) is
    also read and wins when both are set. Do not use `array` under Octane (it is flushed before
    every request); use `redis` to share the cache across containers.
  - `APP_URL` **must be the public URL of your instance**: it is returned as `production_api_url`
    by `/` and `/v4`, used in `llms.txt`, `/v4/ai/manifest.json` and the OpenAPI server, and the
    indexers call `APP_URL/v4/...`.
  - New: `TRUSTED_PROXIES`, `RATE_LIMIT_PER_MINUTE` (opt-in, default `0` = off),
    `QUEUE_WORKERS`, `OCTANE_SERVER` (`swoole`), `OCTANE_WORKERS`, `OCTANE_MAX_REQUESTS`,
    `OCTANE_TASK_WORKERS`,
    `CORS_MIDDLEWARE` (still opt-in), `SCRAPE_MISS_TTL`.
  - Renamed: `CACHE_MAGAZINES_EXPIRE` → `CACHE_MAGAZINE_EXPIRE`.
  - Removed (no longer read): `CACHING`, `DB_CACHING`, `CACHE_META_EXPIRE`,
    `CACHE_PRODUCERS_EXPIRE`, `CACHE_404_EXPIRE`, `CACHE_MICROCACHE_EXPIRE`, `SOURCE`,
    `SOURCE_BAD_HEALTH_THRESHOLD`, `SWAGGER_VERSION`, `INSIGHTS`, `INSIGHTS_MAX_STORE_TIME`,
    `CACHE_BYPASS_TOKEN`, and the RoadRunner tuning vars `RR_MAX_WORKER_MEMORY`,
    `RR_MAX_REQUEST_SIZE_MB`, `JIKAN_QUEUE_WORKER_PROCESS_NUM`.
  - `MICROCACHING_EXPIRE` default in `.env.dist` is `60` (was `5`).
- **Docker image.** Rebuilt on `php:8.4.27-cli-alpine` with `ext-swoole` (was
  `php:8.1.27-bullseye` + RoadRunner 2.12.3) and runs as the non-root `jikanapi` user. `docker-compose.yml` builds the image locally
  (`jikan-rest:local`) instead of pulling `jikanme/jikan-rest`.
  - The entrypoint generates an `APP_KEY` when none is set (set `APP_KEY`/`APP_KEY__FILE` in
    production so it is stable), runs `config:cache` at start (routes are cached at build time),
    starts the scheduler with supercronic, starts `QUEUE_WORKERS` `queue:work` processes, then
    `octane:start --server=swoole --port=8080` (with `--workers`, `--task-workers` and
    `--max-requests` from the env). Extra arguments are passed to Octane.
  - The healthcheck is now `GET /up` on port 8080 (was the RoadRunner `:2114/health` plugin).
  - `.rr.yaml` is no longer used; see [Runtime and operations](#runtime-and-operations).
- **RoadRunner removed (Swoole is the only Octane server).** The image, compose file,
  `install.sh` and `script/server` all use Swoole; the RoadRunner packages and binary are gone.
  - Removed env vars: `OCTANE_RR_CONFIG`, `OCTANE_ROADRUNNER_COMMAND`,
    `OCTANE_ROADRUNNER_HTTP_MIDDLEWARE` (and the older `RR_*` vars listed above).
  - Added: `OCTANE_TASK_WORKERS` (default `1`; Octane's internal tick needs one task worker).
  - Set `OCTANE_SERVER=swoole` (the default) or remove the variable; a leftover
    `OCTANE_SERVER=roadrunner` fails to start.
  - If you maintained a custom `.rr.yaml`, its settings have no effect any more. Worker count and
    recycling map to `OCTANE_WORKERS` / `OCTANE_MAX_REQUESTS`; anything else (TLS, HTTP/2, static
    files, request size limits) belongs in your reverse proxy, or in
    `config/octane.php` → `swoole.options` (merged over Octane's Swoole defaults).
  - Bare-metal installs need `ext-swoole` (`pecl install swoole`).

### Required one-time steps

1. **Run migrations** to create the new indexes: `php artisan migrate` (or
   `docker compose exec app php artisan migrate`). The entrypoint does not run it for you. See
   `docs/DB_INDEXES.md`.
2. **Refresh data stored by 4.2.x.** Documents already in MongoDB keep the values the old parser
   produced until they are re-fetched or their cache expires. Either let caches expire
   (`CACHE_DEFAULT_EXPIRE`, default 1 day; user data 5 minutes) or re-run the indexers
   (`indexer:anime`, `indexer:manga`, `indexer:genres`, `indexer:common`; see
   `docs/POST_FULL_INDEX.md`). Affected values:
   - single-episode `aired` dates (were `+09:00`, now UTC midnight);
   - manga relations (gain the related-entries table: Spin-Off, Other, ...);
   - user friends `friends_since` (nullable) and profile `about` (`null` instead of `""`);
   - user statistics above 999 (no longer cut at the thousands separator);
   - user profile last updates (keep their progress);
   - review pagination (`/users/{u}/reviews` reports `has_next_page` correctly).
3. If you use Typesense, re-import: `php artisan scout:import "App\Anime"` (and the other models,
   see `docs/POST_FULL_INDEX.md` §3).

### What API consumers will notice

Routes, parameters and response keys are unchanged. Differences in values:

- More fields are `nullable` in the OpenAPI spec and can be `null` (e.g. unknown totals in
  `/v4/{anime,manga}/{id}/userupdates`, user statistics fields, `friends_since`, profile `about`).
  See [Behaviour changes](#behaviour-changes).
- `data.scores[].percentage` in `/v4/{anime,manga}/{id}/statistics` is a decimal (`1.4`).
- Invalid enum query parameters (`order_by`, `type`, `status`, `filter`, ...) still return 400,
  but the message now reads `The <field> must be one of: a, b, c.` instead of naming a PHP enum
  class.
- `/v4/{anime,manga}/{id}/news` and `/v4/{anime,manga}/{id}/userupdates` paginate correctly
  (`has_next_page` is `false` on the last page; manga userupdates returns rows again).
- `production_api_url` in `/` and `/v4` is **this instance** (`APP_URL` + `/v4/`), no longer a
  hard-coded public URL.
- `error`/`trace` are empty in error bodies when `APP_DEBUG=false`.

---

## Reference: changes since the Lumen 9 release

This release moves jikan-rest from Lumen 9 to Laravel 13 (PHP 8.4+, Octane/Swoole).
The public **v4 JSON contract is unchanged** for valid requests. This page lists everything
an operator needs to know that is *not* a contract change. Each section notes what it was
on the Lumen release ("before") and what it is now.

**PHP 8.4.4 or newer is required** (`composer.json` `php: ^8.4.4`; jikan v5 refuses 8.4.0-8.4.3, which have an HTML5-parser bug, php-src GH-17481, that can corrupt UTF-8); the Docker image and CI use 8.4.

## New and changed environment variables

| Variable | Before | Now |
|---|---|---|
| `REPORTING`, `REPORTING_DRIVER` | `REPORTING=true` + `REPORTING_DRIVER=sentry` registered Sentry | **Same contract.** Sentry is registered only when both are set; it is excluded from package auto-discovery. Default `false`. |
| `SENTRY_LARAVEL_DSN`, `SENTRY_TRACES_SAMPLE_RATE` | placeholder DSN, `0.5` | empty DSN, `0.1` |
| `MICROCACHING_EXPIRE` | default `60` | default `60` (an interim revision of this branch had changed it to `5`) |
| `RATE_LIMIT_PER_MINUTE` | did not exist (no throttling) | **New, default `0` = disabled.** Per-IP requests per minute for `/v4`. Enable only with a shared cache (`CACHE_DRIVER=redis`; the default `file` store is shared only within one container, and `array` is flushed before every Octane request) and `TRUSTED_PROXIES`. |
| `TRUSTED_PROXIES` | did not exist | **New.** Comma-separated IPs/CIDRs of your reverse proxy / load balancer, or `*`. Needed so `request()->ip()` is the real client. Empty = trust nothing. |
| `QUEUE_WORKERS` | RoadRunner ran one `laravel_queue_worker_1` process (`JIKAN_QUEUE_WORKER_PROCESS_NUM`) | **New, default `0`.** The container entrypoint starts this many `queue:work` processes. Set it if you use `SCOUT_QUEUE=true` or dispatch jobs. |
| `CACHE_MAGAZINES_EXPIRE` | documented in `.env.dist` | The endpoint TTL map reads **`CACHE_MAGAZINE_EXPIRE`** (singular). The plural name was only consumed by the cache-refresh jobs, which were removed. |
| `CACHING`, `DB_CACHING`, `CACHE_META_EXPIRE`, `CACHE_PRODUCERS_EXPIRE`, `CACHE_404_EXPIRE`, `CACHE_MICROCACHE_EXPIRE`, `SOURCE`, `SOURCE_BAD_HEALTH_THRESHOLD`, `SWAGGER_VERSION` | in `.env.dist` | No longer read by the application (several were only consumed by the removed cache-refresh jobs); remove them from your environment. |
| `INSIGHTS`, `INSIGHTS_MAX_STORE_TIME` | enabled the unfinished insights feature | Removed together with the feature (see below). |

## Runtime and operations

| Topic | Before (Lumen + `lumen-roadrunner`) | Now (Laravel + Octane/Swoole) |
|---|---|---|
| Worker pool | `.rr.yaml`, tuned through `RR_MAX_WORKER_MEMORY`, `RR_MAX_REQUEST_SIZE_MB`, `JIKAN_QUEUE_WORKER_PROCESS_NUM` | `OCTANE_WORKERS` (default `auto` = one per CPU), `OCTANE_MAX_REQUESTS` (default `500`, workers are recycled after this many requests) and `OCTANE_TASK_WORKERS` (default `1`). `.rr.yaml` is no longer used. |
| RoadRunner memory supervisor / max request size | `RR_MAX_WORKER_MEMORY` (128), `RR_MAX_REQUEST_SIZE_MB` (256) | **Not mapped.** Use `OCTANE_MAX_REQUESTS` to bound worker memory growth; Swoole's request size limit is Octane's default `package_max_length` (10 MB), overridable in `config/octane.php` → `swoole.options`. |
| Queue workers | `laravel_queue_worker_1` RoadRunner service | `QUEUE_WORKERS=N` (see above). |
| `APP_KEY` | not needed by Lumen | The container entrypoint generates one on first boot when none is supplied. Set `APP_KEY` (or `APP_KEY__FILE`) explicitly in production so it is stable across restarts. |
| Scheduler | all indexers `->daily()` at 00:00 | Staggered `dailyAt()` between 00:10 and 01:30, with `withoutOverlapping()->onOneServer()`. `onOneServer` needs a cache store shared by all instances (`CACHE_DRIVER=redis`); with a container-local store (`file`, the default, or `array`) it only de-duplicates within one container. |

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
- **Unknown totals are `null`.** With jikan v5 an unknown total in
  `/v4/anime/{id}/userupdates` and `/v4/manga/{id}/userupdates` (MyAnimeList shows `5/?`) is `null`
  instead of `0`; the manga `volumes_read`/`chapters_read` are `null` for a "-" cell. The OpenAPI
  schemas mark these fields `nullable: true`. Clients that treated `0` as unknown must check `null`.
- **Score percentages are decimals.** In `GET /v4/anime/{id}/statistics` and
  `GET /v4/manga/{id}/statistics`, `data.scores[].percentage` is now a decimal number such as
  `1.4` (jikan v5 parses it as a float); the Lumen release returned integers. Whole values are
  still serialised without a fraction (`25`), so clients must accept any JSON number there. The
  OpenAPI schema already documents it as `number` (`float`).
- **Operational setup.** Dependencies are resolved against PHP 8.4 and `ext-mongodb` ≥ 2.5.
  A fresh clone needs `composer install`, which relies on the tracked
  `bootstrap/cache/.gitignore` placeholder.
