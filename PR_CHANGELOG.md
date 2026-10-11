# PR Changelog — jikan-rest modernization (Lumen 9 → Laravel 12 → 13)

> **Living document.** Updated as work progresses. This is the accumulated summary
> for the eventual upstream PR(s) to [jikan-me/jikan-rest](https://github.com/jikan-me/jikan-rest).
> The push plan/playbook lives in [`UPSTREAM_PR.md`](UPSTREAM_PR.md); upstream
> blockers live in [`UPSTREAM_ISSUES.md`](UPSTREAM_ISSUES.md).
>
> - Branch: `laravel-12` (local fork; nothing has been pushed upstream)
> - Proof points: parity harness, PHPUnit suite, live endpoint checks

---

## Summary

Migrated jikan-rest from **Lumen 9** (archived Feb 2023; Laravel 9 security-EOL Feb 2024)
onto a supported Laravel release with modern runtimes, a first-party search engine,
working API docs, and a green test suite. Also fixed a latent router bug that made
every dynamic endpoint 404 on Symfony 7, and documented an upstream scraper breakage.

**Headline numbers**

| Metric | Before | After |
|---|---|---|
| Framework | Lumen 9.1.5 | Laravel **13.35.0** |
| PHP | 8.4.1 (Herd-Lite, cannot upgrade `ext-mongodb`) | 8.4.26 (Homebrew `php@8.4` + `pecl mongodb` 2.5.4) |
| Mongo driver | `jenssegers/mongodb` 3.9 | `mongodb/laravel-mongodb` 5.11 |
| Test suite | 280 tests / 2673 asserts / **1 pre-existing failure** | same baseline after migration (PHPUnit **12**) |
| Parity harness | 60 endpoints baseline | **69 → 3** diffs (3 = 2 intentional + 1 pre-existing) |
| Search | 3rd-party Typesense driver pinned to an RC | **Laravel Scout's native Typesense engine** (`typesense-php` stable 6.0.0) |

---

## What changed

### 1. Framework: Lumen 9 → Laravel 12 → **13**
- `laravel/lumen-framework` → `laravel/framework` (12.69.3, then **13.35.0** once Scout shipped a
  native Typesense engine — see §5).
- New `bootstrap/app.php`, `bootstrap/providers.php`, `public/index.php`, `artisan`.
- `routes/web.v4.php` split into `routes/api.php` (root, `/v1`–`/v3` stubs, v4 group)
  + `routes/console.php` (schedule).
- Lumen base classes swapped for Laravel equivalents (exceptions handler, controller,
  console kernel → auto-discovered commands, event providers, `User` model, CORS
  middleware response factory).
- `PackageVersions\Versions` → `Composer\InstalledVersions`.
- PHP constraint raised to `^8.3` (Laravel 13 requirement; runtime is 8.4.26).
- **PHPUnit bumped `^11` → `^12`** (matches Laravel 13's skeleton):
  - PHPUnit 12 removed docblock metadata — migrated all ~34 `@dataProvider` /
    `@test` annotations to `#[DataProvider]` / `#[Test]` attributes (incl. docblocks
    that mixed prose with `@dataProvider`, which moved to an attribute above the
    docblock) and added the `PHPUnit\Framework\Attributes\*` imports.
  - PHPUnit 12 made `TestCase::__construct()` final — moved the
    `searchIndexModelCleanupList` per-test setup out of constructors into `setUp()`.
  - Removed the dead `Tests\IntegrationTestListener` (implemented the PHPUnit-10-era
    `TestListener` API and type-hinted the removed `\Laravel\Lumen\Application`) and
    its ignored `<listeners>` node in `phpunit.xml`; DB isolation is provided by the
    `SyntheticMongoDbTransaction` trait.

### 2. Dependencies
**Added**
- `mongodb/laravel-mongodb` ^5 (was `jenssegers/mongodb` 3.9 — renamed upstream)
- `laravel/octane` ^2, `laravel/scout` ^11 (native Typesense engine), `laravel/tinker` ^3
- `sentry/sentry-laravel` ^4, `spatie/laravel-data` ^4, `spatie/laravel-enum` ^3.3
- `darkaonline/l5-swagger` ^11 + `zircote/swagger-php` ^6
- `symfony/browser-kit/css-selector/dom-crawler/http-client` ^7, `predis/predis` ^2.3
- `spiral/roadrunner-cli` + `spiral/roadrunner-http` (dev / Octane)

**Removed**
- Lumen-only / abandoned: `pushrbx/lumen-roadrunner`, `darkaonline/swagger-lume`,
  `flipbox/lumen-generator`, `ocramius/package-versions`, `laravel/legacy-factories`,
  `fabpot/goutte` (replaced, see below), `matchish/laravel-scout-elasticsearch`
  (Typesense-only search decision)
- `typesense/laravel-scout-typesense-driver` (replaced by **Scout's native engine** —
  removes the `illuminate ^12` ceiling and the `typesense-php 6.1.0-RC2` pin)

**Replaced / shims**
- `fabpot/goutte` (abandoned; caps `symfony/mime` ≤ 6). Goutte 4's `Client` is a thin
  wrapper over Symfony `HttpBrowser`, so we ship `app/Compat/Goutte/Client.php` and
  declare `"replace": {"fabpot/goutte": "4.0.3"}` to satisfy `jikan-me/jikan` v4 on
  Symfony 7. Longer term: upgrade to `jikan-me/jikan` v5 (drops Goutte).

### 3. Critical fix: route parameter constraints (Symfony 7)
Symfony 7 only accepts **word characters** in inline route patterns. `{id:\d+}` failed
to parse and compiled as a literal → a route regex with **zero variables** → every
`/{id}` endpoint 404'd despite appearing in `route:list`. Converted all inline patterns
to bare parameters plus `->where()` (`{id}` + `->where('id','[0-9]+')`, optional params
as `{param?}`). Parity went 69 → 3 problems.

### 4. Spatie Laravel Data v3 → v4 API adoption
- `Cast::cast()` / `DataPipe::handle()` / `prepareForPipeline()` signature changes
  (arrays not `Collection`, `CreationContext`, `ValidationStrategy` enum).
- `DataFromSomethingResolver::execute()` now takes a `CreationContext`.
- Restored `use PreparesData` on the query-parameter traits (removed during signature
  fixes) and taught `PreparesData` to treat bare boolean query flags (`?sfw`,
  `?continuing` — which normalise to `""`/`null`) as `true` **before** validation,
  matching `ContextualBooleanCast` (casts run after validation).

### 5. Search: third-party driver → Scout native Typesense engine
Laravel 13's first-party Scout ships a Typesense engine (incl. `semantic()` / `hybrid()`
search). The third-party `typesense/laravel-scout-typesense-driver` — which had capped
us at `illuminate ^12` and forced `typesense-php` **6.1.0-RC2** — is dropped entirely.

- `composer.json`: removed the driver, added `typesense/typesense-php: ^6.0` (stable
  6.0.0) directly.
- `config/scout.php`: restructured to the engine's `client-settings` shape
  (`Typesense\Client` config) + `max_total_results`.
- `app/JikanApiSearchableModel`: no longer implements the third-party
  `TypesenseDocument` interface (native engine uses standard Scout contracts).
- `app/Services/TypesenseCollectionDescriptor`, `app/Testing/ScoutFlush`,
  `app/Mixins/ScoutBuilderMixin`: rewired to the native `Typesense\Client` /
  `Laravel\Scout\Engines\TypesenseEngine`.
- `AppServiceProvider`: binds `Typesense\Client` (only when `SCOUT_DRIVER=typesense`).
- The app's search middleware (a Scout `$builder->callback`) is compatible with the
  native engine — it invokes the callback with the same `($documents, $query, $options)`
  signature. MongoDB `$text` remains the fallback when no Typesense server is
  configured (`SCOUT_DRIVER` unset) — how local dev runs.

### 6. Runtime
- **PHP**: Herd-Lite's static PHP ships `ext-mongodb` 1.20 which cannot be upgraded
  in place and is too old for Laravel 12's Mongo driver (needs ≥ 1.21). Switched the
  project to Homebrew `php@8.4` + `pecl install mongodb` 2.5.4 via a git-ignored
  `tools/dev/php` wrapper (also unsets `PHP_INI_SCAN_DIR`, which Herd pins in `.zshrc`).
- **Octane** (RoadRunner default / FrankenPHP opt-in) replaces the old
  `lumen-roadrunner` package; `AppServiceProvider::servicesToWarm()/Clear()` ported to
  `config/octane.php` `warm`/`flush`.

### 7. API docs (Swagger)
- `darkaonline/l5-swagger` + `zircote/swagger-php` ^6 (stable).
- `app/Swagger.php` uses **PHP 8 attributes** (`#[OA\Info]`, `#[OA\Server]`) —
  swagger-php 6 doesn't read docblock `@OA\Info` through the attribute analyser.
- Route prefix configured via `defaults.routes.group_options.prefix`; `/docs` redirects
  to the live UI at `/v4/api/documentation`.

### 8. Tests (Lumen → PHPUnit 11/12)
- Moved off `Laravel\Lumen\Testing\TestCase`; `createApplication()` bootstraps via the
  console kernel; added `$response` + `assertStatus()` compat shims.
- `Factory::new()` resolves via the container so contextual bindings apply (Laravel 12
  uses `new static`).
- Restored `setUpTraits()` so the Lumen-era `beginDatabaseTransaction()` (DB truncate +
  Scout flush) actually runs — Laravel's convention expects `begin` + trait short name.
- DB isolation: purge the `mongodb` connection after pointing config at `jikan_tests`
  (a stale connection was truncating the production DB during tests).
- Recreate the squash migration's `$text` indexes after truncate (the test DB is never
  migrated).
- Static data providers (PHPUnit 11), `seeStatusCode`→`assertStatus`,
  `seeJsonStructure`→`assertJsonStructure`.
- Time fixes: `getPreciseTimestamp(3)`/`format('Uv')` may return floats/strings the
  Mongo driver rejects → `getTimestampMs()` / `Carbon::parse(...)`.
- Fix: factory fixtures for `Profile`, `Producers`, `Club` use correct `UTCDateTime` args.

### 9. Known pre-existing / upstream issues (not migration defects)
> All upstream blockers live in [`UPSTREAM_ISSUES.md`](UPSTREAM_ISSUES.md). UP-01…05
> were **filed upstream** (jikan-me/jikan #598–#602, 2026-10-11); fixing them is parked/not targeted locally.
- **`jikan-me/jikan` anime/manga parsers break against current MAL markup.** MAL now
  serves the opening/ending-theme section as a JS widget (no `<table>`), but
  `AnimeParser::getOpeningThemes()/getEndingThemes()` (and manga equivalents) select
  `div.theme-songs.../table/tr` → empty node list → `ParserException` → `500`. Affects
  `/v4/anime/{id}` / `/v4/manga/{id}` and `indexer:anime{,-current-season,-schedule}` /
  `indexer:manga`. Producers/magazines/genres parse fine. Fix belongs in `jikan-me/jikan`.
- `/v4/users/recentlyonline` — `jikan` fails to parse `myanimelist.net/users.php`
  (pre-existing).
- ~~Time-dependent `SeasonControllerTest::testShouldNotIncludeNewlyStartedSeason...`~~ —
  **fixed 2026-10-11**: `getCurrentSeason()` now reads `Carbon::now()` (honours the
  tests' `Carbon::setTestNow()`), the continuing-item fixture pins `episodes = null`
  (was flapping on the factory's random non-null default), and `getItemsBySeason()`
  no longer mutates the caller's `$to`. `--filter SeasonControllerTest` green 20×.

### 10. Config-cache safety + security hardening (from the audit's P0 batch)
- **`env()` → `config()` everywhere outside config** (`grep env(` in `app/ routes/ bootstrap/`
  is now empty, modulo dead `PsrWorkerErrorListener` which is slated for deletion).
  New settings centralized in `config/jikan.php` (scraper timeouts, heartbeat scoring,
  insights, queue delay, Github reporting, rate limit, cache-bypass token) and
  `config/app.php` (`version`). `php artisan config:cache` now works and the app boots
  fully from cached config (was silently nulling `APP_VERSION` etc.).
- **`bootstrap/app.php` gating moved into middleware** — the `withMiddleware` callback
  runs before the config repository is bound (fires on kernel resolution), so
  `config()` there was illegal; `Insights`/`CorsMiddleware` now self-gate at request time.
- **Rate limiting added** (`throttle:v4`) via `RateLimiter::for('v4')` — 240 req/min/IP
  (config: `RATE_LIMIT_PER_MINUTE`), 10 000/min under testing. Parity unaffected
  (headers only, harness compares status+shape).
- **Error responses redacted in production** — `Handler` gates `error` and the raw
  `trace` (file:line) behind `config('app.debug')`; error/report_url still emitted with
  the same shape for parity, values empty when debug is off.
- **`APP_KEY` no longer doubles as a cache-bypass header** — replaced with a dedicated
  `CACHE_BYPASS_TOKEN` (empty by default = no bypass); the cache-refresh jobs send it.

### 11. Phase-2 audit P1 batch (Laravel-13 regressions + latent bugs)
- **MicroCaching `NO_CACHING` bypass was dead on Laravel 13** — Lumen's
  `$request->route()[1]['uses']` no longer works (Route isn't array-accessible), so
  `RandomController@*` / `InsightsController@main` were never excluded from micro-caching.
  Rewired to `$request->route()->getActionName()` (verified: resolves to
  `RandomController@anime` etc., matching the list).
- **Removed dead/broken cache-refresh jobs.** `UpdateCacheJob`/`UpdateDatabaseJob`
  resolved an unbound `app('GuzzleClient')`, `UpdateDatabaseJob` used an unassigned
  `$requestUri`, and nothing dispatched either; deleting them (the self-HTTP +
  `sleep()` + token-header pattern is Octane-hostile anyway). Cache warming should be
  re-designed as an in-process command if revived.
- **CRITICAL test-isolation fix: tests were truncating the *production* `jikan` DB.**
  Our overridden `setUpTraits()` is invoked by Laravel's `setUpTheTestEnvironment()`
  (dynamic dispatch) during `parent::setUp()` — before the TestCase's own `setUp()`
  swaps the mongodb connection to `jikan_tests` — so `beginDatabaseTransaction()`
  truncated real data once per test, on top of the intended test-DB truncation.
  Fixed with a hard guard in `SyntheticMongoDbTransaction`: truncation now refuses to
  run unless the resolved database equals the isolated test DB (`jikan_tests`).
  Verified with sentinel documents across a full suite run (production doc survives,
  test DB truncated). This had caused repeated "re-seed needed" DB loss during local
  development (producers/magazines/genres/anime).
- **Source-heartbeat pruning was a no-op + score could exceed 1.** `unset($fail)` in a
  value-copy `foreach` removed nothing, and the `>=` condition was inverted, so
  `failovers.json` never dropped stale events. Added `SourceHeartbeatProvider::pruneFails()`
  (recency window + size cap, malformed-entry safe) used by provider and listener; score
  denominator is now the real event count (no `-1`).

### 12. Audit-1 P1 batch (Octane correctness, search bugs, lazy handlers)
- **Octane flush set completed** (`config/octane.php`): repository contracts +
  `AnimeGenresRepository`/`MangaGenresRepository` + request-scoped `CacheOptions` and
  `TypesenseCollectionDescriptor` are now flushed per request so cached query builders /
  TTLs can't leak between workers. `EndpointCacheTtlMiddleware` now clears the TTL in a
  `finally` (was skipped when the request threw). Dropped the unused Swoole example table.
- **Scout native-engine chunked pagination corrupted totals.** `jikanPaginate()` merged
  engine pages with `array_merge_recursive`, which turned the scalar `found` (and nested
  hits) into nested arrays → bogus `total` and mangled hit lists for >250-hit queries.
  Rewritten to `array_merge` over `hits` only, keeping `found` scalar.
- **Mongo `$text` fallback + filters: verified, not a bug.** Audited claim that
  `MongoSearchService` drops `filterParameters` was a false positive — both paths go
  through `repository->filter()` → `scopeFilter`. Empirically confirmed the compiled
  Mongo query for `?q=…&type=tv&status=complete` contains `status`/`type` clauses plus
  the `$text` where. No code change needed.
- **`IncrementalIndexer` correctness** (`indexer:incremental`): loop used the resume
  `$index` constant instead of `$i` (re-scraped one id repeatedly) and never advanced
  the resume offset; the id-diff direction was inverted (kept removed ids instead of
  fetching new ones); success/failed bookkeeping was inverted too. All fixed.
- **`/v4/insights` + `/v4/insights/trends` feature removed** (product decision — unused).
  Deleted `InsightsController`, the global `Insights` recording middleware, the
  `Insights*`/`Trends*` resources, the routes, the `insights_*` config/env keys, the
  `MicroCaching` NO_CACHING entry, and its parity baseline entry. `/v4/insights*` now
  returns 404.
- **Custom `CorsMiddleware` removed.** It was a Lumen leftover (re-wrapped Symfony
  responses into plain `Response`s) layered on top of Laravel 13's default global
  `HandleCors`, which already serves CORS from `config/cors.php`. Deleted the class +
  its bootstrap append + the `cors_middleware_enabled`/`CORS_MIDDLEWARE` config/env;
  CORS now works via the framework default (single-pass).
- **Observability polish** (audit-1 §9): log channels renamed off `lumen.log`
  (`jikan.log` / `daily/jikan.log`), deprecations now recorded to `daily` instead of
  `null`, stale config comment removed; Sentry `release` derives from
  `config('app.version')` and `traces_sample_rate` defaults to 0.1 (env-overridable);
  added an `/up` health/liveness route (the `withRouting(health:)` option is ignored
  when a custom `using` closure is used, so it's registered explicitly).
- **Lazy request-handler resolution.** `DefaultMediator` no longer receives all ~65
  constructed handlers per request. A `request (DTO) class => factory closure` map (101
  entries) is built once per worker; each request constructs only the handler it
  dispatches. `DefaultMediatorTest` updated to the factory-map contract.

### 13. Backlog batch (heartbeat storage, TTL map, Dockerfile, static analysis/CI)
- **Source-heartbeat storage moved from flat files to MongoDB (audit-1 P1 #10).**
  The per-request `failovers.json` + `source_failover.lock` / `_last_downtime`
  read-modify-write scheme (multi-worker race-prone under Octane) is replaced by
  atomic storage in the Mongo `failovers` / `failovers_meta` collections
  (`insertOne` per event; lock + last-downtime as one doc per key). The recency
  window + size cap still run through `SourceHeartbeatProvider::pruneFails()`
  (now on in-memory arrays); enable/disable semantics unchanged. The `/` and `/v4`
  `myanimelist_heartbeat` SHAPE is preserved (verified live + via Octane). New
  `Tests\Unit\SourceHeartbeatProviderTest` covers the window/cap/score/lock logic.
- **`per_endpoint_cache_ttl` re-synced + stale-key guard test (audit-1 P1 #11).**
  Dead keys removed (`AnimeController@characters_staff`, `PersonController@seiyuu`,
  `MagazineController@resource`, `GenreController@mainAnime/mainManga`); every
  registered v4 `V4DB` controller action now has an entry (added the previously
  missing `full`/`relations`/`themes`/`external`/`streaming`/`now`/`Random*`… keys).
  `Tests\Unit\CacheTtlMapTest` derives the expected `Controller@action` set from the
  route table (matching `HttpHelper::getRouteName()`) and asserts completeness in
  both directions, so a future route addition fails the suite instead of silently
  falling back to `CACHE_DEFAULT_EXPIRE`.
- **Docker image reworked for the native runtime (audit-1 P0 #1).** New multi-stage
  Dockerfile: `php:8.4-cli-alpine` (Octane workers are CLI processes) + pecl
  `ext-mongodb` + `pcntl`/`sockets`/`opcache`; RoadRunner binary **2025.1.15**
  (amd64/arm64) — the version `spiral/roadrunner-cli ^2.7` fetches via
  `octane:install`; `composer install --no-dev`; `route:cache` baked at build
  (verified safe incl. the closure + user-list routes), `config:cache` deferred to
  the entrypoint so per-deployment env applies; HEALTHCHECK on the `/` app root
  (no `/up` route at the time — see §11's observability follow-up which adds one);
  entrypoint launches `octane:start`. `.dockerignore` refreshed. **Not built on this
  machine** (no Docker) — see the commit's verification checklist.
- **PHPStan/Larastan + Pint + CI matrix (audit-1 P2 #16).** `require-dev` gains
  `larastan/larastan ^3.13` (Laravel-13 compatible), `phpstan/phpstan ^2.3`,
  `laravel/pint ^1.32`. `phpstan.neon` scans `app/` at **level 5** (level 6 → 1000+
  issues on the Lumen-era code; 5 settles at 748) with an 816-entry
  `phpstan-baseline.neon` so analysis exits 0 — bump + regenerate as code is
  type-lined. `pint.json` = Laravel preset. **Pint policy:** the whole tree is not
  Pint-clean (≈400-file diff), so this batch formats only the files it touches and
  CI's `pint --test` is scoped to those paths; the repo-wide format is an explicit
  follow-up. `.github/workflows/ci.yml` adds a PHP 8.3/8.4 matrix with a Mongo
  service, lint, phpstan, pint, and `phpunit --no-coverage`
  (`DB_DATABASE=jikan_tests`, `CACHE_DRIVER=array`, `SCOUT_DRIVER` unset).

---

## Verification

- **Unit + integration**: `php vendor/bin/phpunit --no-coverage`
  → **292 tests, 2707 assertions, 0 failures** (first fully-green run). The
  time-dependent `SeasonControllerTest` flake was removed (see follow-ups) and
  the new random-endpoint regression tests (+3) pass. Earlier baseline while the
  flake existed: 280–289 tests / 2673–2696 assertions with 1–2 season failures.
- **Parity harness** (`tools/parity/{snapshot,compare}.php`, baseline committed):
  **69 → 3** differences at the green check; re-verified after the data wipe and the
  P0/P1/practice fixes — **21 endpoints match the baseline byte-for-byte, including the
  seeded genres/magazines/producers**, and the changes introduced **no new shape or
  status diffs**. Every remaining difference is explained by documented blockers:
  anime/manga/characters/people → 500 `ParserException` (upstream MAL markup change),
  list endpoints empty without anime data, `/docs` 301 redirect, `users/recentlyonline`
  pre-existing failure. The harness now masks the heartbeat `score` (int/float
  depending on recorded events).
- **Live smoke** (native runtime): `/`, `/v4`, `/v4/genres/anime`, `/v4/seasons/now`,
  `/v4/schedules?filter=...`, `/v4/producers/{id}`, `/v4/api/documentation` all `200`.
- **Octane**: `php artisan octane:start --server=roadrunner` serves endpoints `200`;
  FrankenPHP available via `OCTANE_SERVER=frankenphp`.
- **Octane soak (2026-10-11)**: 12 iterations × 10 endpoints (~130 requests) with
  varied query strings against RoadRunner 2025.1.15 — identical status codes every
  iteration, byte-identical repeated payloads, correct pagination across pages, zero
  error/panic/worker-restart lines in the log, `/up` → 200. Confirms the flush set
  (repos, `CacheOptions`, mediator, Mongo heartbeat) leaks no state between requests.

### 14. Verification + hardening batch (2026-10-11, parallelised)
- **Random endpoints fixed** — `QueryRandom{Anime,Manga}Handler` and the
  `QueryRandomItemHandler` family passed a null `->first()` into a Resource when the
  underlying collection was empty (visible only with no indexed data), crashing with
  "Attempt to read property ... on null". Now `abort(404)` in the standard error shape;
  regression tests cover empty → 404 (both families) and seeded → 200.
- **Mongo index audit (`DB_INDEXES.md`)** — all 134 squash-migration indexes are
  present in the live `jikan` DB (0 missing); proposed compound indexes for
  schedule/season/top queries (decision pending).
- **Test-DB `$text` index swap fixed** — `ensureSearchIndexes` had `characters`
  (`{name,name_kanji}`) and `people` (`{name,given_name,family_name}`) swapped vs the
  migration; corrected + made drop-then-create so a stale index definition can never
  silently survive a test run.
- **`.env.dist` env-key sync** — 68 config keys added (App/locales, Logging, Caching,
  Redis/Queue, Session/Auth, Mail, Scout/TypeSense, Swagger, Sentry, Octane…), 12
  orphaned keys removed, 4 documented-but-commented keys (empty value would clobber a
  non-empty default).

---

## Not done / follow-ups
- Rector config (not requested here) + repo-wide Pint formatting (see §13 policy).
- **`SeasonControllerTest` de-flaked (2026-10-11)** — no more wall-clock dependence or
  seeded-data flakiness: `QueryCurrentAnimeSeasonHandler::getCurrentSeason()` honours
  `Carbon::setTestNow()` via `Carbon::now('Asia/Tokyo')`; the continuing-item fixture
  now pins `episodes = null` (deterministic "long-running" branch match instead of the
  factory's random `episodes` coin-flip); `DefaultAnimeRepository::getItemsBySeason()`
  computes "last day of this month" on a clone instead of mutating the caller's `$to`.
  Verified `--filter SeasonControllerTest` green 20× (incl. random order). Baseline
  suite target: 289 tests / 2696 assertions, all green.
- CI: Renovate/Dependabot; validate the PHP 8.3 matrix + Mongo service on GitHub
  Actions (runner-level; logic verified locally), and a full Typesense integration
  run once a server is available.
- Patch or await upstream fix for the MAL theme-markup parser breakage.
- Verify the native Typesense engine end-to-end against a real Typesense server
  (docker-compose) and reconcile search-index behavior/ranking parity.