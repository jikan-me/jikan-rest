# jikan-rest — Laravel 13 Conformance Audit

**Audited:** `/Users/irfan/Developer/jikan-rest` (fork of `jikan-me/jikan-rest`)
**Runtime:** Laravel `13.35.0` · PHP `8.4.26` (Homebrew `php@8.4`) · `mongodb/laravel-mongodb 5.11.0` · Octane `2.21.0` (RoadRunner) · Scout `11.9.0` (native Typesense) · `phpunit 12.5.38` (installed) — all from `composer.lock`
**Method:** static reading of source only; no app code was modified; no runs/tests executed. Line numbers are from the current working tree.
**Date:** 2026-10-11

---

## Executive summary (by severity)

### P0 — fix before this is a viable deployment target
1. **The shipped Docker image cannot build the project.** `Dockerfile` is still `php:8.1.27-bullseye` + RoadRunner `2.12.3`, while `composer.json` requires `php ^8.3` and `laravel/framework ^13` / `mongodb/laravel-mongodb ^5` both require ≥ PHP 8.2/8.3. `composer install` in the image fails platform checks (Laravel 13 needs ≥ 8.3). (`Dockerfile:4`, `composer.json:8`)
2. **~50 `env()` calls outside config files**, several at bootstrap/middleware/route-load time (`bootstrap/app.php:26,30`, `routes/api.php:24`, `routes/web.v4.php:8,380`, plus app services, jobs, indexers, controllers, exception handler). `php artisan config:cache` (a stated check item) silently turns all of these into `null`; the app is effectively **not config-cache safe**, and several responses / flags depend on `env()` at request time (`APP_VERSION` in the root and `/v4` payloads would render `null`).
3. **No application-level rate limiting.** The public, read-only, scraping API has no `throttle` middleware/RateLimiter anywhere (`grep throttle` only finds `config/auth.php:28`, unused). A client can hammer the scraper into hitting MyAnimeList on a cold cache.

### P1 — perf / Octane correctness / architecture
4. **Octane flush set is incomplete for stateful singletons.** Repositories are singletons that cache query builders (`RepositoryQueryBase:10-11,26-47`) yet are *not* in `config/octane.php` `flush`; `CacheOptions` (request-scoped TTL singleton) is not flushed and can leak a stale TTL when a request throws between `setTtl()` and `setTtl(null)` (`EndpointCacheTtlMiddleware:22-31`, `bootstrap`/`AppServiceProvider:104`). Cross-request state corruption is possible under persistent workers.
5. **Per-request file I/O for heartbeat + logging.** `SourceHeartbeatMonitor` middleware fires a `SourceHeartbeatEvent` on **every** request; `SourceHeartbeatListener::handle()` rewrites `failovers.json` (read → json_decode → append → write) on every request, and the listener constructor opens a Monolog `StreamHandler` **per listener instance** (`SourceHeartbeatListener:27-28,51-73`). Multi-worker Octane ⇒ read-modify-write races that can corrupt the file. The `/` route also reads + parses `failovers.json` on every hit (`routes/api.php:32-37` → `SourceHeartbeatProvider:31-68`).
6. **Eager construction of all ~65 request handlers per request.** `AppServiceProvider::registerRequestHandlers()` (`AppServiceProvider:166-343`) gives `DefaultMediator` the *entire* constructed handler list via a contextual binding; `Mediator` is `bind()`ed and flushed each request (`octane.php:147`), so under Octane every request rebuilds ~65 handler/service graphs before dispatching to one. Use a lazy DTO→handler map instead.
7. **Scout pagination chunking can corrupt totals.** `ScoutBuilderMixin::jikanPaginate()` (for >250 hits) merges engine pages with `array_merge_recursive` (`Mixins/ScoutBuilderMixin.php:41-42`); the native `TypesenseEngine` does **not** implement `PaginatesEloquentModels` (verified in `vendor/laravel/scout/src/Engines/TypesenseEngine.php`), so this legacy branch *is* the Typesense path, and `found` becomes an array after merging ⇒ `getTotalCount()` = `(int)['found'…]` ⇒ bogus `total`. Only triggers >250 hits; untestable locally (no Typesense server) — must be verified against a real server or fixed to sum pages.
8. **Mongo `$text` fallback search drops filters.** `MongoSearchService::search()` never applies `filterParameters` (`Services/MongoSearchService.php:9-35`); only the Scout path does (`SearchEngineSearchService:17-23`). With `SCOUT_DRIVER` unset (local default = `null` driver, `config/scout.php:18`), `?q=…&genres=…&score=…` returns **unfiltered** results. Suspected pre-existing; needs a parity test to confirm scope.

### P2 — hygiene
9. Dead code referencing removed packages/classes: `PsrWorkerErrorListener` imports `pushrbx\LumenRoadRunner…LoopErrorOccurredEvent` (package removed) (`Listeners/PsrWorkerErrorListener.php:7,21`); `IntegrationTestListener` type-hints `\Laravel\Lumen\Application` and implements `PHPUnit\Framework\TestListener` (removed in PHPUnit 10; the `<listeners>` node in `phpunit.xml:38-40` is ignored by PHPUnit 11/12) ⇒ dead, non-functional listener.
10. Legacy route style: string controller actions + group `namespace` (`routes/api.php:54-60`, all of `routes/web.v4.php`) still work but are the pre-modern form; duplicate `/v4/producers` definition (`routes/web.v4.php:276-289` **and** `:291-293`); env-dependent route registration (`web.v4.php:380`, `DISABLE_USER_LISTS`) is not route-cache-stable.
11. Stale/missing per-endpoint cache TTL map (`config/jikan.php:14-151`); dead `elasticsearch` block in `config/scout.php:159-178`; DB DSN built from `env()` at config load (`config/database.php:3-10`).
12. Composer hygiene: stale `"name": "laravel/lumen"`; unused `amphp/http-client` (drag in abandoned `league/uri-parser`), `danielmewes/php-rql dev-master`, `mirazmac/dotenvwriter`; no `config.platform` PHP pin; `minimum-stability: dev` only needed for `php-rql`. `PR_CHANGELOG`/`MIGRATION_PLAN` doc drift (they claim PHPUnit stays ^11 — lock has **12.5.38**; the PHPUnit-12 follow-up is mostly already done).

**Intentional / acceptable (audited, not flagged as wrong):** the feature/handler/mediator + repository layer is the app's design; controllers are thin and validation lives in spatie/laravel-data DTOs — anti-patterns are limited to construction efficiency and Octane flushing. Scraper results cached in Mongo with `request_hash` is the design. The GitHub auto-report URL (`GithubReport`) is deliberate public-API behavior (see Findings §6).

---

## Findings by area

### 1. Laravel 13 conventions & bootstrap

| File:Line | What | Why it matters | Fix |
|---|---|---|---|
| `bootstrap/app.php:18-24` | `withRouting` registers only `commands` + a `using:` closure; **no `health` entry** | No `/up` health route under php-fpm; container HEALTHCHECK (`Dockerfile:67`) targets RoadRunner 2114 (Octane-only) | `->withRouting(health: '/up', …)` or a tiny health route |
| `bootstrap/app.php:26-38` | `env('INSIGHTS')`, `env('CORS_MIDDLEWARE')` read at bootstrap; middleware appended conditionally | `env()` outside config; breaks under `config:cache`; makes global middleware set depend on runtime env | Gate via `config('jikan.*')`/`config('app.*')` |
| `bootstrap/app.php:40-42` | `withExceptions()` is empty because rendering is bound via `AppServiceProvider::register()` (`AppServiceProvider:85-88`) | Bypasses Laravel 13's exceptions config (Sentry mapping, `shouldRenderJsonWhen`, throttling, `register()`) and forces a custom `Handler` singleton | Move rendering into `withExceptions();` keep `Handler` only as a rendering strategy |
| `routes/api.php:54-60` + `routes/web.v4.php` (all) | `Route::group([…'namespace' => …])` + string actions `'AnimeController@main'` | Still supported, but opaque, IDE/route:list-unfriendly, and the last legacy piece of the Lumen migration | Convert to class references `Route::get('/anime', [SearchController::class,'anime'])` and route attributes; mechanical, parity-protected by `tools/parity` |
| `routes/api.php:20-39` vs `routes/web.v4.php:4-23` | The root-info JSON payload is **duplicated** (an almost identical `/` closure outside the v4 group and inside it) and reads `env('APP_VERSION')` | Drift risk; `env()` in a response (see §10); heartbeat read on every root request | Single `InfoController` or config-backed data; `config('app.version')` |
| `routes/web.v4.php:276-293` | `/v4/producers` defined twice: group root `'/'` (`:281-283`) **and** standalone `'/producers'` (`:291-293`) | Duplicate route registration; which one wins depends on matching order & trailing-slash normalization | Delete one (keep group). Pre-existing legacy, but cheap to fix |
| `routes/web.v4.php:380` | `if (env('DISABLE_USER_LISTS') === false)` gates user-list routes at **route-load time** | Not stable across `route:cache`; `env()` in routes | Always register; return 404/403 in controller based on `config()` |

### 2. Config correctness

| File:Line | What | Why it matters | Fix |
|---|---|---|---|
| `config/database.php:3-10` | DSN assembled from `env()` including `DB_USERNAME`/`DB_PASSWORD`; username fallback uses `env("APP_ENV")` | Credentials resolved at config load and embedded in DSN; `env()` outside config; the `admin`-auth assumption is undocumented | Use `DB_DSN` env or let the Mongo driver build a MongoDB URI from structured config; test path explicitly |
| `config/scout.php:18` | `'driver' => env('SCOUT_DRIVER', 'null')` | With `SCOUT_DRIVER` unset the app runs the Mongo `$text` fallback — the *intended* local mode, but the default makes a fresh clone silently skip search-engine features; also `typesense` `client-settings` default `api_key` is `'abcd'` (`:136`) and there is a leftover `elasticsearch` block (`:159-178`) | Default to `null` intentionally but document; drop elasticsearch block; require `TYPESENSE_API_KEY` |
| `config/jikan.php:14-151` | `per_endpoint_cache_ttl` contains **stale keys** (`AnimeController@characters_staff` :19, `GenreController@mainAnime`/`mainManga` :113-114, `PersonController@seiyuu` :64) and **misses** many real endpoints (`AnimeController@{relations,themes,external,streaming}`, `MangaController@{relations,external}`, `SeasonController@now`, `WatchController@*`, `UserController@{full,userupdates}`, `CharacterController@full`, `PersonController@full`, `ClubController@{staff,relations}`, all `Random*`) | Cache TTL is 𝘦𝘲𝘶𝘪𝘷𝘰𝘬𝘦𝘯𝘵 per endpoint: missing entries silently fall back to `CACHE_DEFAULT_EXPIRE`; stale keys are dead string-matches (route names come from `HttpHelper::getRouteName`) | Re-sync the map against `route:list` / add a test that every registered controller action has an entry |
| `config/octane.php:133-149` | `warm`/`flush` ported; **repositories and `CacheOptions` are missing from `flush`**; unused Swoole example table left (`:162-167`) | See P1 #4 — request-scoped mutable singletons not reset | Add repository contracts + `CacheOptions::class` to `flush`; drop example table |
| `config/logging.php:59,65` | Channels still write to `storage/logs/lumen.log` / `daily/lumen.log` | Cosmetic, but "lumen" naming is misleading post-migration; default `LOG_LEVEL=debug` in `.env.dist` | Rename to `laravel.log`/`jikan.log`; set prod level to `warning` |
| `config/sentry.php:53,57` | `traces_sample_rate` defaults to `0.0`; `release` = `env('APP_VERSION')` | Sentry tracing off by default; release breaks under config cache | `config('app.version')`; sensible default e.g. `0.1` when `SENTRY_DSN` set |
| `config/cache.php:16` | default store `file`; `.env` sets `CACHE_DRIVER=array` | Micro caching under `array` store is per-process (useless across workers/requests) locally — fine locally, but easy to misread | OK as-is; docs |

### 3. Container / service providers & Octane safety

| File:Line | What | Why it matters | Fix |
|---|---|---|---|
| `AppServiceProvider:85-97` | `singleton(ExceptionHandler::class, Handler::class)`; `env('SOURCE_TIMEOUT',1)`; `instance('HttpClient'|'JikanParser'|'SerializerV4')` | Shadow `env()` defaults (note `SourceTimeout` **1s here vs 5s in `Handler` and `10s` in `.env`** — three different defaults), `env()` in register; string-keyed container instances are untraceable | Bind typed contracts; read timeout from config; recommend `bind`/`scoped` for request-scoped scrapers |
| `AppServiceProvider:100-101` | `SearchAnalyticsService` chosen by `env("APP_ENV")` at register time | With `config:cache`, `env()` is null ⇒ `DefaultSearchAnalyticsService` bound even in testing | `app()->environment()` or config |
| `AppServiceProvider:108-114` | `Typesense\Client` singleton **only when driver === "typesense"** | Good; but the `TypesenseCollectionDescriptor` singleton (`:113`) caches collection details in-memory and depends on the client — fine under Octane | Consider `bind` until first use |
| `AppServiceProvider:141-164` | All repositories `singleton`; `UnitOfWork` singleton | Repos cache query builders (`RepositoryQueryBase:10-11,26-47`) ⇒ **cross-request builder state** unless flushed each request; only `JikanUnitOfWork`/`Mediator`/`SearchService` are flushed (`octane.php:142-149`) | Flush repository contracts (or make builders non-cached, or `scoped()`) |
| `AppServiceProvider:166-343` | Registrar builds all ~65 handlers in the `give()` closure returned as an array to `DefaultMediator(RequestHandler ...)` | Rebuilt on every `Mediator` resolution (per request under Octane); heavy | Lazy `when()->needs()` per concrete DTO, or a static DTO→handler map built once |
| `AppServiceProvider:118-139` | `getSearchService()` still has an `ElasticScoutSearchService` branch (`:125-126`) referencing removed `matchish` engine | Dead path; `ElasticScoutSearchService` class still exists (`Services/ElasticScoutSearchService.php`) but nothing sets `scout.driver` to that value | Delete branch + class |
| `EndpointCacheTtlMiddleware:22-31` + `CacheOptions` singleton | Sets TTL before `$next`, resets after; **no reset on exception**; `CacheOptions` not in Octane flush | Stale TTL from a failed request leaks into the next request on the same Octane worker (affects scraper-cache expiry + cache headers) | `finally`-style reset and/or flush `CacheOptions` per request |

### 4. Routing & controllers

Overall: controllers are thin (good). Validation is centralized in DTO attributes; `limit` is properly capped at `max_results_per_page` via `app/Rules/MaxResultsPerPageRule.php` and `MaxLimitWithFallback` (`app/Dto/Concerns/HasLimitParameter.php`). No fat controllers found besides `InsightsController`.

| File:Line | What | Why it matters | Fix |
|---|---|---|---|
| `Controllers/V4DB/InsightsController.php:16-155` | `env()` read per request; `/trends` ignores `?limit` for the aggregate (`:134-135` uses `$maxResultsPerPage` unconditionally); `total` is a full `DB::table('insights')->count()` (`:149`) | Expensive per request; buggy limit handling; env-dependent gating (an endpoint exists but returns 403 when disabled) | Gate via config/middleware; respect limit; use count from aggregation or maintain counters |
| `Http/Middleware/Insights.php:32-50` | `terminate()` does `DELETE … WHERE timestamp < now-2d` then inserts a row **on every request** | Full-scan delete + insert per request; the `@todo scaling` comment acknowledges it; under Octane `terminate()` still runs per request | Replace with scheduled cleanup job; batch inserts |
| `HttpHelper.php:20-28` | `requestType()` uses `$request->segments()[1]` — crashes on root/`/v1`–`/v3`? | Root route bypasses cache middleware (`MicroCaching:56-61`), so largely benign; keep | Add a guard when running middleware on root |
| `MicroCaching.php:51` | `$request->header('auth') === env('APP_KEY')` bypass | **`APP_KEY` is used as a shared secret HTTP header**; jobs send it (`Jobs/UpdateCacheJob.php:62-65`, `UpdateDatabaseJob.php:54-57`). Anyone with `APP_KEY` bypasses caching and the key doubles as an API token in the open | Replace with a dedicated `CACHE_BYPASS_TOKEN`, or remove the bypass for public commands |
| `Handler.php:218-227` | Generic fallback returns `'trace' => "file:line"` and `'error' => $e->getMessage()` **unconditionally**; `GithubReport` stringifies the exception into the `report_url` | Debug info + absolute file paths + exception messages exposed to any client even with `APP_DEBUG=false` (see §6) | Keep error shape for parity but gate `error`/`trace` behind `APP_DEBUG`/feature-flag; sanitize paths |

### 5. Models / Eloquent / Mongo

| File:Line | What | Why it matters | Fix |
|---|---|---|---|
| `JikanApiSearchableModel.php:47-50` | `searchableAs()` = `strtolower(table).'_index'` + `env("APP_ENV")==='testing'? '_testing'` | `env()` in a model method (config-cache unsafe); index-name collision risk if two models share `$table` (e.g. a future `common` searchable); `_testing` suffix is a blunt multi-env isolation | Derive index from config + model; use Scout `prefix` (`config/scout.php:31`) for env isolation |
| `Anime.php:61-63` (+ Manga, Character, …) | `$hidden = ['_id','request_hash','expiresAt', …]` — **but not `createdAt`/`modifiedAt`** | Search/list payloads built from Mongo documents can include `createdAt`/`modifiedAt` `UTCDateTime` values; `DefaultQueryBuilderService:40-42` only unsets `_id` | Add `createdAt`/`modifiedAt` to each model's `$hidden` and cover with a response-structure test |
| `JikanApiModel.php:25-64` | `scopeRandom` uses `$aggregate(['$sample'=>…])` | Fine for Mongo; `$sample` produces no stable order — intentional random | — |
| `DatabaseRepository.php:55-75` | `insert()` uses `new $modelClass()` + `fill()` + manual meta fields (documented `fixme`) | Bypasses `Model::create()` mass-assignment protection by design; attributes not in `$fillable` are silently dropped; a future schema field could vanish from indexer writes | Use `forceFill()` + comment, or widen `$fillable` and use `::create()` |
| `Anime.php:155-217`, `filterByProducer(s)` closures type-hint `MongoDB\Laravel\Eloquent\Builder` | In the Scout path filters run against the **DB query** inside the Scout `query` callback (`SearchEngineSearchService:17-23`) — OK, but the closure signature diverges | Works; just surprising. N+1 is not an issue on the model side; the only "N+1-ish" cost is `DefaultCachedScraperService` issuing 2–3 queries per lookup (`find()` = get → scrape → get) | Acceptable; consider `with`-less design is intentional |
| `JikanSearchable.php:45-61` | `queryScoutModelsByIds` builds `whereIntegerInRaw(mal_id, …)` | Fine for Mongo; `(int)` cast on ids; non-`int` key-type path untested | — |

### 6. Security

| File:Line | What | Why | Fix |
|---|---|---|---|
| (app-wide) | **No rate limiting** (see Exec #3); auth guard/session config are unused leftovers (`config/auth.php:5-13`, `config/session.php:4`) | Public endpoint can drive the scraper; upstream production has Redis-based throttling — not present here | Add `throttle` middleware (`RateLimiter::for('api', …)` + `Limit::perMinute(…)`) on all `v4` routes; revisit when upstreaming |
| `Handler.php:63-227` | Custom render leaks exception message + `file:line` in prod (`:223-224`, `:96`, `:134`, `:166`, `:179`, `:192`, `:204`), plus GitHub report URL containing the raw trace (`GithubReport:114-128`) | Information disclosure; generated URLs crawl the network if clients click them; `trace`/`error` never gated by `APP_DEBUG` | Redact `error`/`trace` when `!config('app.debug')`; keep `report_url` (intentional) but strip absolute paths from trace text |
| `MicroCaching.php:51`, `Jobs/UpdateCacheJob.php:62-65` | `APP_KEY` as header token | Key leakage → cache-bypass and it's the crypto key; also appears in worker logs when debugging | Dedicated token or drop the bypass |
| `public/index.php:34-35` | `ob_start('ob_gzhandler')` | Double-gzip if proxy/CDN also compresses; not used under Octane; gzip of explicit `Content-Encoding` responses misbehaves | Remove (Rely on RoadRunner/httpd compression) |
| `config/cors.php:5` | `allowed_origins => ['*']` | Read-only GET API, intentional; but `Access-Control-Allow-Origin: *` + `X-Request-Fingerprint` headers fine | Keep; document |

### 7. Performance

| File:Line | What | Why | Fix |
|---|---|---|---|
| `Mixins/ScoutBuilderMixin.php:28-61` | Chunked search-engine pagination; `array_merge_recursive` on pages (>250 hits); `$found` key becomes an array; `forPage` on DB | Typesense native engine never hits the `PaginatesEloquentModels` fast path ⇒ totals/pagination wrong for popular queries (only testable against a real server) | Sum pages explicitly; or always use engine `paginate` respecting `max_total_results`; verify with Typesense |
| `Listeners/SourceHeartbeatListener.php:27-28,66-70` | New Monolog StreamHandler per listener; `failovers.json` read+write per request | fd churn; disk I/O on hot path; multi-worker corruption | Cache handler as a static; move scoring to an in-memory + occasional flush; or use DB/Redis |
| `routes/api.php:32-37` (`SourceHeartbeatProvider:31-68`) | Root `/` & `/v4` hit the filesystem for heartbeat score | Cheap but on hot root route; also `env()` in score calc | Memoize per worker for the `warm` period |
| `octane.php:133-140` | `warm` includes `ScoutSearchService`, `ScoutBuilderPaginatorService`, `EloquentBuilderPaginatorService` | Warming is fine; ensure they flush/rebuild correctly (see flush gaps) | Re-audit with a soak test |
| `database/migrations/…squash.php` | Text indexes named `search` on anime/manga/people/characters/magazines/producers/clubs; many single-field indexes | `$text` search only possible on these; verify they actually exist in the dev DB (the migration is the *only* one; test DB recreates them after truncate) | Run `mongosh` index checks; consider compound indexes for `(airing, type, broadcast)` schedule queries |
| `config/cache.php:16` + `.env:62` | `CACHE_DRIVER=array` in local | Micro-cache ineffective locally (fine); in prod use Redis | Document prod cache driver |

### 8. Testing

| File:Line | What | Why | Fix |
|---|---|---|---|
| `composer.json:45`, `composer.lock` | `phpunit/phpunit ^12.0` **installed 12.5.38** | `PR_CHANGELOG`/`MIGRATION_PLAN` claim PHPUnit stays on ^11 — **stale docs**; suite reportedly ran on ^11 (280 tests). PHPUnit 12 **is** the reality; must re-verify green | Update docs; run suite; then the "migrate annotations" follow-up is effectively done — tests already use `#[DataProvider]` attributes (34 found) and there is no `__construct` in `tests/TestCase.php` (final-safe) |
| `phpunit.xml:38-40` | `<listeners><listener class="Tests\IntegrationTestListener"/></listeners>` | `TestListener` API removed in PHPUnit 10 (verified: no `TestListener*` in `vendor/phpunit`); PHPUnit’s XML migrate step strips the node ⇒ **the listener never runs** ⇒ the `migrate:fresh`/`migrate:rollback` it was meant to do never happens; tests rely on the trait-based truncation. Class also type-hints the removed `\Laravel\Lumen\Application` (`IntegrationTestListener.php:14`) — would fatal if ever loaded | Delete the listener + the `<listeners>` block; keep DB isolation in the traits (documented in `tests/TestCase.php:186-201`) |
| `tests/TestCase.php:175` | DB purge + `setUpTraits()` override | Deliberate, works; watch for confusion on fresh clones | Comment, done |
| Coverage gaps | Indexers (`app/Console/Commands/Indexer/*`), `Exceptions/Handler` rendering paths, all middleware except none, Octane-specific lifecycle, the Typesense chunk-pagination path, and the Mongo `$text`+filters combo are untested | Scraper layer is the highest-risk area and is only lightly covered (`DefaultCachedScraperServiceTest`) | Unit-test Handler rendering; add a Mongo `$text` filter test; add an Octane worker-cycle test; add a Typesense integration test when a server is available |

### 9. Observability

| File:Line | What | Fix |
|---|---|---|
| `config/logging.php:19-32,50-113` | Single `stack`→`daily` channel; `deprecations`→`null`; logs still at `lumen.log`; no request log | Add `LOG_CHANNEL` structure, request-logging middleware or Laravel’s `log` pattern, structured context |
| `config/sentry.php:53` | `traces_sample_rate=0` default; no sampling decision | Set a real default; wire `release` from config; confirm `sentry` binding only when DSN set (`Handler.php:236`) |
| (none) | No health endpoint in app bootstrap (`bootstrap/app.php:18-24` lacks `health:`); Octane offers `/octane/health` only when running via Octane | Add `/up` |
| `Listeners/SourceHeartbeatListener.php` | Source health tracked in a flat JSON file — no atomic writes, no lock | Move to Mongo `failovers` collection or Redis; atomic update |

### 10. 12-factor / env

- `.env.dist` and `.env` are in sync (diffs are only `APP_ENV/APP_DEBUG/APP_KEY/APP_URL` + `OCTANE_SERVER`); `.env` is gitignored (good — real `APP_KEY` is not in source).
- `APP_VERSION` is duplicated in `.env` (`"4.2.2"`), read via `env()` in responses, and re-specified as a literal in `app/Swagger.php:6`. Should be a single `config('app.version')` derived from `InstalledVersions`.
- `config/scout.php:136` default Typesense key `'abcd'`; `config/sentry.php:5` placeholder DSN in `.env.dist` — both acceptable defaults but should be required in prod.
- `artisan` (`artisan` file) and `public/index.php` are Laravel 11/13-standard (modulo the `ob_gzhandler` shim).
- `SourceHeartbeatProvider`/`Insights`/`Handler`/`Jobs`/indexers all read `env()` at runtime — see P0 #2 for the central fix.

### 11. Dependency hygiene

| File:Line | Dep | Verdict |
|---|---|---|
| `composer.json:12` | `amphp/http-client ^4.6` | Unused in code; drags abandoned `league/uri-parser` (via `amphp/socket`). Remove ⇒ abandonment warning gone |
| `composer.json:13` | `danielmewes/php-rql dev-master` | Unused, ancient; **forces** `minimum-stability: dev` (`:79`). Remove ⇒ can drop dev stability |
| `composer.json:21` | `mirazmac/dotenvwriter` | No usages found. Remove |
| `composer.json:34` | `vlucas/phpdotenv ^5` | Redundant — Laravel 13 framework already depends on it. Remove |
| `composer.json:2-3` | `"name": "laravel/lumen"`, `"description": "The Laravel Lumen Framework."` | Stale identity after Laravel 13 migration |
| `composer.json:81-88` | No `config.platform` PHP pin | Pin `"platform": {"php": "8.4.26"}` (or 8.3) so lock stays reproducible |
| `composer.json:37-39` | `"replace": {"fabpot/goutte": "4.0.3"}` + `app/Compat/Goutte/Client.php` | Intentional shim for `jikan-me/jikan` v4; long-term fix is jikan v5 — keep, document |
| `composer.json:58-62` | RR CLI in dev + `spiral/roadrunner-http` in require | Correct shape for Octane; ensure Dockerfile uses the same versions |

### 12. Details (delete-Laravel-native-shims)

| File:Line | What | Verdict |
|---|---|---|
| `app/Support/helpers.php:6-41,72-116` | Shims for `config_path`, `rescue`, `app_path`, `cache` | **Delete.** Native Laravel provides all four; `function_exists` guards make them dead. Keep `to_boolean`, `max_results_per_page`, `text_match_buckets`, `ensureEnumPrimitiveValue` (`:43-70,118-125`). Remove the file from `composer.json` `autoload.files` (`:54-56`) after deleting |
| `bootstrap/cache/services.php`, `packages.php` | Generated service manifests present in tree (gitignored) | Normal; not config-cache — fine |
| `app/Listeners/ExampleListener.php`, `app/Events/ExampleEvent.php` | Lumen-era scaffolding | Delete (unused; `SourceHeartbeatListener` imports `ExampleEvent` accidentally at line 5) |
| `app/Console/Commands/CacheRemove.php`, `routes/console.php:12-22` | Scheduler contents | Fine; indexers return can't run until jikan parser breakage is fixed upstream (see below) |

---

## Prioritized action plan

### P0 — security & correctness (do first)
| # | Action | Ref | Effort | Target |
|---|---|---|---|---|
| 1 | Rework Dockerfile to `php:8.4` base + current RoadRunner (or FrankenPHP) + `ext-mongodb ≥ 1.21`; update HEALTHCHECK to the app's health path | `Dockerfile:4,9,14` | ½–1 d | **Upstream PR** (after RFC) |
| 2 | Quarantine all ~50 `env()` calls into config: add `config/jikan.php` (already exists — extend), swap middleware/bootstrap/controllers/jobs/indexers/Handler/Provider to `config()` reads; make `APP_VERSION` a config value derived from `InstalledVersions` | Exec #2 + §1/§2/§6 | 1–2 d | **Local first**, then upstream |
| 3 | Add API rate limiting: `RateLimiter::for('v4', …)` + `throttle` on the v4 group; document values | — | ½ d | Upstream PR |
| 4 | Redact exception `error`/`trace` when `APP_DEBUG` is false (keep the `report_url` shape for parity); stop shipping messages/files in JSON | `Handler.php:218-227,96,166,204` | ½ d | Upstream PR |
| 5 | Replace the APP_KEY auth-header bypass with a dedicated cache-bypass token | `MicroCaching.php:51` | ½ d | Local + upstream |

### P1 — architecture / perf / Octane
| # | Action | Ref | Effort | Target |
|---|---|---|---|---|
| 6 | Octane flush: add repository contracts + `CacheOptions` to `octane.flush`; make `EndpointCacheTtlMiddleware` reset TTL on exception; add an Octane cycle test | §3, §7 | 1 d | Local |
| 7 | Lazy handler resolution (link DTO→handler without constructing all ~65 per request) | `AppServiceProvider:166-343` | 1–2 d | Upstream PR |
| 8 | Fix Scout chunked pagination (`sum found`, avoid `array_merge_recursive`) + verify Typesense path against a real server | `ScoutBuilderMixin:28-61` | 1 d + spike | Upstream PR |
| 9 | Mongo `$text` fallback: apply `filterParameters` in `MongoSearchService::search` or fail loudly; add a parity test for `q`+filter | `MongoSearchService.php:9-35` | 1 d | Local + upstream |
| 10 | Replace heartbeat `failovers.json` per-request I/O with Redis/Mongo atomic scoring; memoize root-route heartbeat | §7 | 1 d | Upstream PR |
| 11 | Re-sync `config/jikan.php` TTL map + add stale-key guard test | §2 | ½ d | Local |

### P2 — hygiene
| # | Action | Ref | Effort | Target |
|---|---|---|---|---|
| 12 | Delete shim helpers, `Example*` scaffolding, `IntegrationTestListener`, `PsrWorkerErrorListener`; delete `<listeners>` from `phpunit.xml`; update PR_CHANGELOG (PHPUnit is already 12) | §8, §12 | ½ d | Local |
| 13 | Routes: class-reference actions / route attributes; drop duplicate `/v4/producers`; always-register user-list routes | §1 | 1 d | Upstream PR |
| 14 | Composer: remove unused deps (`amphp/http-client`, `danielmewes/php-rql`, `mirazmac/dotenvwriter`, `vlucas/phpdotenv`), fix `name`/`description`, pin `config.platform`, drop `minimum-stability: dev` | §11 | ½ d | Upstream PR |
| 15 | Config cleanup: remove elasticsearch block; `api_key` default; `lumen.log` → `jikan.log`; Sentry defaults; add `health: /up`; remove `ob_gzhandler` | §2/§6 | ½ d | Local + upstream |
| 16 | PHPStan/Larastan + Pint + CI matrix (PHP 8.3/8.4, Mongo 8, Typesense) | `PR_CHANGELOG` §Not-done | 2–3 d | Upstream PR |

**Effort on a scoped fork**: P0 ≈ 2–4 d, P1 ≈ 3–5 d + Typesense spike, P2 ≈ 1–2 d.

---

## Intentional / acceptable (do not "fix" blindly)
- **Feature/handler/mediator + repository + DTO-validation architecture** — deliberate; quality is consistent; only construction cost (§3 #7) and Octane flushing (§3/#6) are called out.
- **Scraper caching in Mongo keyed by `request_hash`** with `createdAt/modifiedAt/expiresAt` meta — design; the hidden-field gap (missing `createdAt/modifiedAt` in `$hidden`) is the only model concern.
- **`jikan-me/jikan` parser breakage** (theme `<table>` markup) and `/users/recentlyonline` failure — **upstream issues**, not migration defects (PR_CHANGELOG §9).
- **Goutte shim + `replace`** — an accepted bridge until jikan v5.
- **GitHub report-URL on errors** — intentional public-API aid; still recommend redacting trace detail in prod.
- **`$text` Mongo search as the no-Typesense fallback** — by design; keep, but fix the dropped filters (§#9).
- **Parity preservation** — route/response shape intentionally preserved byte-for-byte in v4; do not "modernize" controller responses while claiming parity.
---

## Resolution status (living; updated as fixes land)

| P-item | Status | Where |
|---|---|---|
| P0 #1 Dockerfile | ✅ done | `9bd115c` — php:8.4-cli-alpine + RR 2025.1.15 multi-stage Dockerfile + entrypoint/HEALTHCHECK rework (§13) |
| P0 #2 env()→config | ✅ done | commit "P0 correctness batch"; `config:cache` verified booting |
| P0 #3 rate limiting | ✅ done | `throttle:v4` + `RateLimiter::for('v4')` (240/min; 10k/min testing) |
| P0 #4 error/trace redaction | ✅ done | `Handler` gates behind `config('app.debug')` |
| P0 #5 APP_KEY bypass | ✅ done | `CACHE_BYPASS_TOKEN` (empty = no bypass), jobs updated |
| P1 #6 Octane flush gaps | ✅ done | repos + CacheOptions flushed; TTL finally-reset |
| P1 #7 lazy handlers | ✅ done | mediator DTO→factory map built once/worker |
| P1 #8 Scout chunked total | ✅ done | array_merge hits, scalar found |
| P1 #9 Mongo `$text` filters | ✅ verified not a bug | filters applied via `scopeFilter` (empirically confirmed) |
| insights feature | ✅ removed (unused; `/v4/insights*` → 404) |
| CORS middleware re-wrap | ✅ removed (framework `HandleCors` + `config/cors.php`) |
| P1 #10 heartbeat storage | ✅ done | `d1df28f` — Mongo `failovers`/`failovers_meta`, atomic inserts; flat files gone; `/`+`/v4` heartbeat SHAPE unchanged |
| P1 #11 TTL map + guard | ✅ done | `fd59cb2` — `per_endpoint_cache_ttl` re-synced (dead keys removed), `CacheTtlMapTest` guards both directions |
| P2 #16 PHPStan/Pint/CI | ✅ done | `4b780d8` — Larastan ^3.13 (Laravel-13 ok) @ level 5 + baseline, Pint (Laravel preset, scoped `--test`), ci.yml matrix |
| SeasonControllerTest flakiness | ✅ done | `getCurrentSeason()` honours `Carbon::setTestNow()` (`Carbon::now('Asia/Tokyo')`); continuing fixture pins `episodes=null`; `getItemsBySeason()` stops mutating caller's `$to`. `--filter SeasonControllerTest` green 20× (incl. random order) |
| P1 … | ⏳ | see phase-2 audit + backlog |
| Swagger UI / l5-swagger | ✅ removed | product decision (2026-10-11) — §7 kept as historical record |
