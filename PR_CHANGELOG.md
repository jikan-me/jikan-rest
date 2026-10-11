# PR Changelog — jikan-rest modernization (Lumen 9 → Laravel 12 → 13)

> **Living document.** Updated as work progresses. This is the accumulated summary
> for the eventual upstream PR(s) to [jikan-me/jikan-rest](https://github.com/jikan-me/jikan-rest).
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
- **`jikan-me/jikan` anime/manga parsers break against current MAL markup.** MAL now
  serves the opening/ending-theme section as a JS widget (no `<table>`), but
  `AnimeParser::getOpeningThemes()/getEndingThemes()` (and manga equivalents) select
  `div.theme-songs.../table/tr` → empty node list → `ParserException` → `500`. Affects
  `/v4/anime/{id}` / `/v4/manga/{id}` and `indexer:anime{,-current-season,-schedule}` /
  `indexer:manga`. Producers/magazines/genres parse fine. Fix belongs in `jikan-me/jikan`.
- `/v4/users/recentlyonline` — `jikan` fails to parse `myanimelist.net/users.php`
  (pre-existing).
- Time-dependent `SeasonControllerTest::testShouldNotIncludeNewlyStartedSeason...`
  fails today (failed on the Lumen baseline too).

---

## Verification

- **Unit + integration**: `php vendor/bin/phpunit --no-coverage`
  → **280 tests, 2673 assertions**, with 1–2 failures — the time-dependent
  `SeasonControllerTest` season tests (fail/flag intermittently against the
  Mongo `$dateDiff` + `Carbon::setTestNow` season logic; one of them also failed on
  the original Lumen baseline). A `tearDown()` reset was added so `Carbon::setTestNow`
  doesn't leak between tests.
- **Parity harness** (`tools/parity/{snapshot,compare}.php`, baseline committed):
  **69 → 3** differences; remaining 3 = `/docs` → Swagger redirect (intentional) +
  `users/recentlyonline` (pre-existing parser bug).
- **Live smoke** (native runtime): `/`, `/v4`, `/v4/genres/anime`, `/v4/seasons/now`,
  `/v4/schedules?filter=...`, `/v4/producers/{id}`, `/v4/api/documentation` all `200`.
- **Octane**: `php artisan octane:start --server=roadrunner` serves endpoints `200`;
  FrankenPHP available via `OCTANE_SERVER=frankenphp`.

---

## Not done / follow-ups
- PHPStan/Larastan + Pint + Rector config.
- CI matrix (PHP 8.3/8.4, Mongo 8, current Typesense) + Renovate/Dependabot.
- `Dockerfile` rework from `php:8.1.27-bullseye` + RR 2.12.3 to the new runtime.
- Patch or await upstream fix for the MAL theme-markup parser breakage.
- Verify the native Typesense engine end-to-end against a real Typesense server
  (docker-compose) and reconcile search-index behavior/ranking parity.