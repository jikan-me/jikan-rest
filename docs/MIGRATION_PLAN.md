# Migration Plan: Lumen 9 → Laravel (12 → 13)

Status: **planning draft** (untracked). Not started.

## 1. Objective

Move jikan-rest off `laravel/lumen-framework` ^9 (discontinued; Lumen archived Feb 2023, Laravel 9 security-EOL Feb 2024) onto a supported, first-party Laravel release, while preserving the public v4 API contract for valid requests (guarded by the parity harness and the integration suite).

**Target: Laravel 12 first (PHP 8.4), then bump to Laravel 13.**

### 1.1 Why 12 first — and what 12 → 13 actually costs

The 12 → 13 *code* diff for **this** app is nearly nil. Jikan is a stateless, read-only JSON API: no CSRF, no sessions, no Blade views, no auth flows, no MySQL. Almost every "high/medium impact" item in the Laravel 13 upgrade guide doesn't apply:

| Laravel 13 breaking change | Affects jikan? |
|---|---|
| `VerifyCsrfToken` → `PreventRequestForgery` (High) | No — stateless API, no CSRF |
| `serializable_classes => false` (Med) | **Maybe** — only if we cache PHP objects; needs a spike |
| Cache/session prefix naming (Low) | No — unless relying on framework defaults |
| `polyfill-php85` `array_first`/`array_last` global conflicts (Low) | **Maybe** — check custom global helpers |
| Session `serialization: json`, pagination view names, MySQL upsert, notifications (Low) | No |
| `Str` factories reset between tests (Low) | Tests only |

Laravel's own estimate for a 12 → 13 upgrade is **~10 minutes**. So the version gap is not the reason to stage this.

**The real reason to land on 12 first is package support — specifically our search stack.** Verified on Packagist (Oct 2026):

| Package | Latest | Supports Laravel |
|---|---|---|
| `mongodb/laravel-mongodb` | 5.11.0 | **12 | 13** ✅ |
| `laravel/octane` | 2.21.0 | 10–13 ✅ |
| `darkaonline/l5-swagger` | 11.1.0 | 11–13 ✅ |
| `laravel/scout` | 11.9.0 | 9–13 ✅ |
| `sentry/sentry-laravel` | 4.29.0 | up to 13 ✅ |
| `spatie/laravel-data` | 4.23.0 | 10–13 ✅ |
| **`typesense/laravel-scout-typesense-driver`** | latest | **illuminate ^10\|^11\|^12 — no 13** ❌ |

Since **Typesense is the only search engine we keep**, that last row is the blocker: the driver doesn't declare Laravel 13 support yet. So: land the big migration on **12**, ship it, and bump to **13** once the Typesense Scout driver adds `illuminate ^13` (or we swap to a maintained driver). This turns a risky "Lumen 9 → 13" leap into "Lumen 9 → 12" (hard) + "12 → 13" (trivial).

> Lumen is a *subset* of Laravel. Most application code (repositories, services, features/handlers, models, DTOs) is framework-agnostic and needs no changes. The work is concentrated in the **bootstrap, base classes, routes, and packages**.

## 2. Why now

| | Now | Target |
|---|---|---|
| Framework | `laravel/lumen-framework` ^9 (archived) | `laravel/framework` ^12 |
| PHP | `^8.1` (EOL Dec 2025) | `^8.3` / `^8.4` |
| Mongo | `jenssegers/mongodb` ^3.9 | `mongodb/laravel-mongodb` ^5 |
| Runtime | `pushrbx/lumen-roadrunner` 5.9.1 | `laravel/octane` |
| Deprecations | Hundreds of `E_DEPRECATED` on PHP 8.4 | gone |

The deprecation noise, the blocked Mongo driver upgrade (upstream PR #569), and the lack of security fixes all trace back to this one decision.

## 3. Scope: Lumen touchpoints (the actual work)

Everything below is a concrete file/API that must change. Everything *not* listed (≈490 of 504 app files) should survive untouched.

### 3.1 Bootstrap & entrypoint
| File | Current | Change |
|---|---|---|
| `bootstrap/app.php` (272 lines) | `new Laravel\Lumen\Application`, `withFacades()`, `withEloquent()`, `configure()`, `middleware()`, `routeMiddleware()`, `register()`, `router->group()` | Replace with Laravel 11+ fluent `Application::configure()`; move configs to `config/`, providers to `bootstrap/providers.php`, middleware aliases into `withMiddleware()` |
| `public/index.php` | Lumen bootstrap + `ob_start("ob_gzhandler")` + manual header | Laravel's default index.php; gzip via middleware or keep |
| `app/Console/Kernel.php` | extends `Laravel\Lumen\Console\Kernel`, `$commands[]`, `schedule()` | Laravel 11+ has no Console Kernel; commands auto-discovered; schedule → `routes/console.php` |
| `app/Exceptions/Handler.php` | extends `Laravel\Lumen\Exceptions\Handler` | Register via `withExceptions()` in `bootstrap/app.php`; keep the custom rendering logic |

### 3.2 Base classes
| File | Current | Change |
|---|---|---|
| `app/Http/Controllers/V4DB/Controller.php` | `Laravel\Lumen\Routing\Controller` | `Illuminate\Routing\Controller` (or a local base) |
| `app/User.php` | `Laravel\Lumen\Auth\Authorizable` | `Illuminate\Foundation\Auth\User` + `Authorizable` |
| `app/Providers/EventServiceProvider.php` | `Laravel\Lumen\Providers\EventServiceProvider` | `Illuminate\Foundation\Support\Providers\EventServiceProvider` |
| `app/Providers/SourceHeartbeatProvider.php` | `Laravel\Lumen\Providers\EventServiceProvider` | same as above |
| `app/Http/Middleware/CorsMiddleware.php` | `Laravel\Lumen\Http\ResponseFactory` | `Illuminate\Routing\ResponseFactory` |
| `app/Providers/AppServiceProvider.php` | `Laravel\Lumen\Application` type hint; `servicesToWarm()`/`servicesToClear()` | type-hint `Illuminate\Foundation\Application`; warm/flush move to Octane config |
| `app/Support/helpers.php` | shims for `config_path`, `rescue`, `cache`, `app_path` | Delete shims (native in Laravel); keep `to_boolean`, `max_results_per_page`, `text_match_buckets`, `ensureEnumPrimitiveValue` |

### 3.3 Routes & controllers
- `routes/web.v4.php`: **576 lines, 106 route definitions**, grouped as
  `$router->group(['prefix' => 'v4', 'namespace' => 'App\Http\Controllers\V4DB', 'middleware' => [...]])`.
- Laravel 11+ removed the implicit controller namespace, so string actions like
  `['uses' => 'AnimeController@main']` must become class references
  `[AnimeController::class, 'main']` (modern standard), or the group must set a namespace explicitly.
- The v1/v2/v3 stub routes and the `/` info route in `bootstrap/app.php` move into `routes/api.php` / `routes/web.php`.
- 19 controllers in `app/Http/Controllers/V4DB`; their bodies are framework-agnostic except for the base class.

### 3.4 Tests
- `tests/TestCase.php` extends `Laravel\Lumen\Testing\TestCase` → extend `Illuminate\Foundation\Testing\TestCase`; `createApplication()` must return the Laravel app from `bootstrap/app.php`.
- `tests/IntegrationTestListener.php` type-hints `Laravel\Lumen\Application` → `Illuminate\Foundation\Application`.
- Custom testing traits (`App\Testing\*`) are framework-agnostic.

### 3.5 Packages
| Package | Current | Change |
|---|---|---|
| `pushrbx/lumen-roadrunner` | 5.9.1 | **`laravel/octane`** (RoadRunner or FrankenPHP) |
| `flipbox/lumen-generator` | ^9 | **drop** — `serve`, `make:*`, `key:generate` are native |
| `darkaonline/swagger-lume` | ^9 | **`darkaonline/l5-swagger`** |
| `zircote/swagger-php` + `doctrine/annotations` | 3.x / 1.14 | ^5 + **PHP 8 attributes** (drop annotations) |
| `jenssegers/mongodb` | ^3.9 | **`mongodb/laravel-mongodb`** ^5 (namespace + model base class change) |
| `ocramius/package-versions` | ^2.5 | **drop** — use `Composer\InstalledVersions` |
| `fabpot/goutte` (via `jikan-me/jikan`) | ^4 | `symfony/http-client` + `symfony/dom-crawler` |
| `laravel/legacy-factories` | ^1.1 | class-based factories |
| `laravel/scout`, `illuminate/redis`, `spatie/laravel-data`, `sentry/*`, `typesense/*`, `matchish/*elasticsearch` | 9.x / old | auto-discovery in Laravel; bump majors (scout ^11, sentry ^4, data ^4); **drop ElasticSearch entirely — Typesense only** (remove `config/elasticsearch.php`, `ElasticScoutSearchService`, `matchish/laravel-scout-elasticsearch`) |
| `config/roadrunner.php`, `.rr.yaml` | RR 2.7 / 2.12.3 | `config/octane.php` + current RoadRunner/FrankenPHP |

### 3.6 Runtime: Octane with *both* RoadRunner and FrankenPHP

Good news: this is native to Octane. Octane is application-server-agnostic — it ships drivers for `frankenphp`, `roadrunner`, and `swoole`, selected at start time:

```bash
php artisan octane:start --server=roadrunner   # backwards-compatible default
php artisan octane:start --server=frankenphp   # opt-in
# or via env: OCTANE_SERVER=frankenphp
```

Plan:
- Ship **both** binaries in the image (multi-stage: `rr` from the RoadRunner image, `frankenphp` from `dunglas/frankenphp`).
- Default to **RoadRunner** so existing deployments/ports (8080, health on 2114) keep working; FrankenPHP is opt-in via `OCTANE_SERVER`.
- Port the current `servicesToWarm()` / `servicesToClear()` (from `AppServiceProvider`) into `config/octane.php`'s `warm` / `flush` arrays and Octane event listeners (`RequestTerminated`, `OperationTerminated`) — this is the request-scoped reset that matters for the persistent worker model.
- Keep `.rr.yaml` semantics (worker count, relay) mapped into Octane's RoadRunner options.

## 4. Strategy: big-bang on a branch, verified by parity tests

Lumen→Laravel is not meaningfully incremental at the framework level, so:

1. Branch `laravel-12` off `master`; keep `master` as the known-good reference.
2. Stand up the Laravel skeleton and re-point `bootstrap/app.php` (Section 5).
3. Port base classes, helpers, routes, packages in the order of Section 6.
4. **Parity harness**: snapshot the JSON of a representative endpoint set on `master`, then assert identical responses on the branch (structure + values, modulo timestamps). This is the primary safety net given only 27 test files.
5. Cut over only when parity + existing test suite are green.

## 5. Before / after: bootstrap

**Current (`bootstrap/app.php`)** — imperative, Lumen-specific:
```php
$app = new Laravel\Lumen\Application(realpath(__DIR__.'/../'));
$app->withFacades();
$app->withEloquent();
$app->configure('database');
$app->middleware($globalMiddleware);
$app->routeMiddleware(['microcaching' => ...]);
$app->register(\pushrbx\LumenRoadRunner\ServiceProvider::class);
$app->router->group(['prefix' => 'v4', 'namespace' => 'App\Http\Controllers\V4DB', 'middleware' => $commonMiddleware], fn ($router) => require __DIR__.'/../routes/web.v4.php');
return $app;
```

**Target (`bootstrap/app.php`, Laravel 12):**
```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'microcaching' => \App\Http\Middleware\MicroCaching::class,
            'source-health-monitor' => \App\Http\Middleware\SourceHeartbeatMonitor::class,
            'cache-ttl' => \App\Http\Middleware\EndpointCacheTtlMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // port app/Exceptions/Handler.php rendering here
    })
    ->withProviders([
        \App\Providers\AppServiceProvider::class,
        \App\Providers\SourceHeartbeatProvider::class,
        // ... others; framework providers auto-discovered
    ])
    ->create();
```

**Route before → after:**
```php
// before (Lumen, implicit namespace)
$router->get('/anime', ['uses' => 'SearchController@anime']);
// after (Laravel 12, explicit class)
Route::get('/anime', [SearchController::class, 'anime']);
```

## 6. Execution phases

### Phase A — Prep & safety net ✅ done
- [x] Branch `laravel-12`; tag `master` as `pre-laravel-migration`.
- [x] Parity harness built (`tools/parity/`) + baseline captured — 60 endpoints, committed `a335b7b`.
- [x] Existing suite run on **unmodified** code: **280 tests, 2,673 assertions, 1 failure** (pre-existing).
- [ ] Freeze feature work on `master` during migration (process item).

**Baseline facts (Lumen 9, `pre-laravel-migration`)**
- Parity statuses: 55× `200`, 3× `400` (`/v1`–`/v3` discontinued stubs), 1× `403` (`/v4/insights`, `INSIGHTS=false`), 1× `500` (`/v4/users/recentlyonline`).
- Pre-existing bugs to track separately (not migration blockers):
  - `/v4/users/recentlyonline` → `500`: `Call to undefined method App\Dto\QueryRecentlyOnlineUsersCommand::getFingerPrint()`.
  - `SeasonControllerTest::testShouldNotIncludeNewlyStartedSeasonOfAnimeInPreviousSeasons` fails — time-dependent season/`continuing` logic.
- The test suite does **not** wipe the seeded DB (synthetic fixtures are cleaned per-test).

### Phase B — Skeleton & bootstrap (1 wk)
- [ ] Add `laravel/framework` ^12; remove `lumen-framework`.
- [ ] New `bootstrap/app.php`, `bootstrap/providers.php`, `public/index.php`.
- [ ] Move `configure()` calls into `config/` (already present) and provider registration.
- [ ] Convert middleware registration + aliases.
- [ ] Port exception rendering into `withExceptions()`.

### Phase C — App code (1 wk)
- [ ] Swap all base classes (§3.2).
- [ ] Replace `PackageVersions\Versions` → `Composer\InstalledVersions`.
- [ ] Trim `helpers.php` shims.
- [ ] Move `servicesToWarm()/servicesToClear()` → Octane config.
- [ ] Delete `app/Console/Kernel.php`; move schedule to `routes/console.php`.

### Phase D — Routes & controllers (1 wk)
- [ ] Split `bootstrap` routes + `web.v4.php` into `routes/api.php` (+ `web.php`).
- [ ] Convert 106 string actions to class references.
- [ ] Verify every route matches the `master` route table (`php artisan route:list` diff).

### Phase E — Packages & infra (1 wk)
- [ ] Mongo: `jenssegers/mongodb` → `mongodb/laravel-mongodb` (model base class, provider).
- [ ] Swagger: `swagger-lume` → `l5-swagger`; annotations → attributes.
- [ ] Drop `flipbox/lumen-generator`, `ocramius/package-versions`, `laravel/legacy-factories`.
- [ ] Bump scout/sentry/data majors; decide on ElasticSearch driver.
- [ ] Update Dockerfile (php 8.4) + `.rr.yaml`/Octane.

### Phase F — Tests & CI (0.5 wk)
- [ ] Port `TestCase` to Laravel's base; fix `IntegrationTestListener`.
- [ ] CI matrix: PHP 8.3/8.4, Mongo 8, Typesense current; add PHPStan + Pint.

### Phase G — Runtime & cutover (1 wk)
- [ ] Adopt `laravel/octane` (FrankenPHP or RoadRunner); port warm/flush.
- [ ] Parity harness green; run soak/load test.
- [ ] Cut over; tag release; update `container_usage.md` / docs.

## 7. Risk register

| Risk | Likelihood | Mitigation |
|---|---|---|
| Response contract drift | Med | Parity harness on ~40 endpoints; compare before/after |
| `mongodb/laravel-mongodb` v5 behavior/API changes | Med | Dedicated spike; the model layer is the riskiest single swap |
| Octane vs `lumen-roadrunner` semantics (request-scoped state) | Med | Port `servicesToClear()` to Octane `flush`/`RequestTerminated`; load test |
| Swagger annotations → attributes churn | Low | Mechanical; swagger-php 5 has a migration tool |
| Scraper (`jikan-me/jikan`) Goutte deprecation | Med | Separate workstream; `symfony/http-client` already in use |
| Test suite too thin to catch regressions | High | Parity harness is mandatory, not optional |
| Upstream divergence | Low | Rebase periodically; consider contributing the migration back |

## 8. Acceptance criteria
- `composer install` clean on PHP 8.3/8.4 with **no** `--ignore-platform-req`.
- Zero `E_DEPRECATED` from framework/vendor code.
- `php artisan route:list` matches the `master` route table exactly.
- Parity harness: identical JSON on the endpoint set.
- Existing test suite green; CI matrix green.
- Runs under Octane; container image builds and serves `/v4/anime/1`.

## 9. Effort estimate
~6 weeks for one engineer (Phases A–G), of which ~60% is mechanical route/base-class/package work. The two genuine spikes are **Mongo driver v5** and **Octane request lifecycle**.

## 10. Decisions (locked)

1. **Target Laravel 12 first**, then bump to 13 once the Typesense Scout driver supports `illuminate ^13`. (§1.1) *Superseded 2026-10-13: the PR targets Laravel 13 directly (decision 12).*
2. **Runtime: both** — RoadRunner as the backwards-compatible default, FrankenPHP opt-in via `OCTANE_SERVER`. (§3.6)
3. **Search: Typesense only.** ElasticSearch support and its config/services are removed.
4. **Local fork now, upstream contribution intended.** (§11)

Decisions added by the 2026-10-13 audit-fix pass (details and evidence: `docs/PR_CHANGELOG.md`, operator impact: `docs/UPGRADING.md`):

5. **Rate limiting is opt-in.** `RATE_LIMIT_PER_MINUTE` defaults to 0; enabling it requires `TRUSTED_PROXIES` and a shared cache (otherwise every client behind a proxy shares one bucket). The Lumen release had no limiter, so on-by-default was a behaviour change.
6. **Routes are class-based and named** (`v4.<resource>.<action>`); the route name keys `per_endpoint_cache_ttl` and the micro-cache bypass list. `{id}` constraints live in the route-group attributes because `Route::group(...)->where()` is a silent no-op.
7. **Octane request state is per request.** The mediator's handler-factory map is `scoped` and built from the request's container; `RepositoryQueryBase` hands out cloned builders. Nothing that captures a repository may be resolved through `$this->app`.
8. **Operator knobs stay as on the Lumen release:** CORS is opt-in (`CORS_MIDDLEWARE`), Sentry is opt-in (`REPORTING` + `REPORTING_DRIVER`), micro-cache default 60s. The `auth: <APP_KEY>` cache-bypass header is removed (its refresh jobs were deleted).
9. **Strict Eloquent models outside production** (`Model::shouldBeStrict`). It exposed `Profile::$fillable` lacking `last_updates` / `external_links`; completing it means cached `/v4/users/{u}/full` now returns `updates` and `external` like the uncached response.
10. **Lumen test shims are retired** (stateless `getJsonWithParams()`, Laravel `setUp<Trait>` hooks). Test and assertion counts were unchanged by the conversion.
11. **Suite wiring.** `CLAUDE.md` imports `AGENTS.md`; opencode reads the generated AGENTS.md block plus `opencode.json`. Client-UI audit sections (errorHandling, contractHeaders, formRequestValidation, routeModelBinding) are removed from `arch-rules.json` so those audits refuse instead of reporting a false zero. Generated audit baselines are not committed (they embed absolute paths); `baselines/.gitkeep` keeps the directory.
12. **Target Laravel 13 directly** (no 12-first landing) on **PHP 8.4+ only** (`composer.json` `php: ^8.4`; CI and Docker run 8.4). Decided 2026-10-13.
13. **The pre-release parser is accepted:** `jikan-me/jikan ^5.0@beta` is the intended dependency for this Laravel 13 PR and moves to a stable v5 when one ships.
14. **Rate limiting stays in this PR**, opt-in and off by default (decision 5).
15. **Agent/LLM tooling stays in this PR** (`AGENTS.md`/`CLAUDE.md`, `.ai/skills`, `.backend-suite`, `opencode.json`, `.claude/settings.json`, `llms.txt`, the AI manifest). Making the project agentic and LLM-friendly is part of this change, not a follow-up.

## 11. Upstream contribution strategy

The intent is to contribute this back to `jikan-me/jikan-rest`, whose maintainers are conservative and currently stuck on the Lumen/Mongo-driver combination (PR #569). To make the work reviewable:

- **Land an RFC issue first.** Post the plan (this doc, condensed) as a GitHub issue to get maintainer buy-in on target version (12 vs 13) and the Octane switch before writing code. Upstream may prefer a smaller first step.
- **Keep the branch minimal and single-purpose.** Do **not** mix Phase 0 hygiene (PHPStan/Pint/Docker/CI churn) into the migration PR — separate PRs, so each is reviewable and bisectable.
- **Preserve the public API contract exactly.** Upstream cares most about the v4 response shape; the parity harness doubles as the PR's evidence.
- **Stage as a sequence of PRs**, each green:
  1. `chore: add PHPStan + Pint + parity harness` (no behavior change)
  2. `feat: Laravel 12 skeleton + bootstrap` (framework swap, app still boots)
  3. `feat: migrate base classes, routes, helpers`
  4. `feat: mongodb/laravel-mongodb v5`
  5. `feat: swagger-lume → l5-swagger`, drop abandoned packages
  6. `feat: Octane (RoadRunner default + FrankenPHP opt-in)`
  7. `chore: bump to Laravel 13`
- **Rebase on `master` regularly**; keep the diff reviewable and avoid unrelated reformatting (Pint only on touched files during the migration).
- **Offer to co-maintain** the migrated branch if upstream wants to defer the merge.

## 12. Environment notes & Phase B deviations (as executed)

**Runtime switched to Homebrew `php@8.4`.** Herd Lite's PHP 8.4.1 bundles `ext-mongodb` 1.20.0 inside a static binary (no `extension_dir`, no `phpize`), and it cannot be upgraded in place. Laravel 12 requires `mongodb/laravel-mongodb` 5.2+, which requires `ext-mongodb >= 1.21`. Resolution: installed Homebrew `php@8.4` (8.4.26) + `pecl install mongodb` (2.5.4), run via a project-local wrapper `tools/dev/php` (git-ignored) that unsets `PHP_INI_SCAN_DIR` (Herd Lite pins it in `.zshrc`). The global Herd setup is untouched.

**Dependency swaps pulled forward from Phase E** (they cannot coexist with Lumen, so Phase B necessarily includes them):
- `laravel/lumen-framework` → `laravel/framework` ^12 (12.69.3)
- `jenssegers/mongodb` ^3.9 → `mongodb/laravel-mongodb` ^5 (5.11.0)
- `pushrbx/lumen-roadrunner` → `laravel/octane` ^2 (2.21.0)
- `laravel/scout` ^9 → ^11; `sentry/sentry-laravel` ^2 → ^4; `spatie/laravel-data` ^3 → ^4; `predis/predis` ^1 → ^2; `symfony/yaml` ^4 → ^7; `phpunit` ^9 → ^11
- removed: `ocramius/package-versions`, `laravel/legacy-factories`, `matchish/laravel-scout-elasticsearch`, `flipbox/lumen-generator`, `darkaonline/swagger-lume`

**Goutte replaced by a local shim.** `fabpot/goutte` (via `jikan-me/jikan` v4) caps `symfony/mime` at `^6.0`, which cannot coexist with Laravel 12's Symfony 7.2. Since Goutte 4's `Client` is a thin wrapper over Symfony's `HttpBrowser`, we add `app/Compat/Goutte/Client.php` and declare `"replace": {"fabpot/goutte": "4.0.3"}` in `composer.json`. The eventual clean fix is `jikan-me/jikan` v5 (which drops Goutte), but that is a major API change and out of scope for now.

**Swagger implemented** (the earlier RC blocker is resolved — `zircote/swagger-php` ^6 is now stable at 6.12.0):
- `darkaonline/l5-swagger` ^11 + `zircote/swagger-php` ^6.
- `app/Swagger.php` uses **PHP 8 attributes** (`#[OA\Info]`). swagger-php 6 does not read docblock
  annotations through the attribute analyser, so a docblock `@OA\Info` alone yields
  "Required @OA\Info() not found". The pre-existing `@OA\Get` docblocks in the controllers still work.
- Route prefix must go in `defaults.routes.group_options.prefix`.
- `/docs` redirects to the live Swagger UI at `/v4/api/documentation`.
- Regenerate after annotation changes: `php artisan l5-swagger:generate`.

**TypeSense driver** resolved to `typesense/laravel-scout-typesense-driver` 5.2.10 (the only version supporting Scout ^11), which pulls `typesense/typesense-php` 6.1.0-RC2. Pin to a stable release when available.

**Developer tooling (executed later):** `laravel/boost` ^2 (dev, first-party) added to power the Laravel marketplace skills integration — `php artisan boost:add-skill …` installed four skills under `.ai/skills/` (`laravel-specialist`, `php-pro`, `laravel-security`, `laravel-patterns`). Pure dev tooling; excluded from the runtime image (`composer install --no-dev`).

### Critical fix: route parameter constraints

Symfony 7 (used by Laravel 12) only accepts **word characters** in inline route patterns. `{id:\d+}` fails to
parse and compiles as a literal string, producing a regex with *zero* variables — so the route never
matches despite appearing in `route:list`. This 404'd every lookup endpoint.

Correct Laravel 12 idiom is a bare parameter plus a `where()` constraint:
```php
// BEFORE (broken on Symfony 7)              // AFTER (works)
'anime/{id:[0-9]+}'                          'anime/{id}'  + ->where('id', '[0-9]+')
'/history[/{type:[A-Za-z]+}]'                '/history/{type?}' + ->where('type', '[A-Za-z]+')
```
Optional parameters use `{param?}` (trailing `?` inside the braces), not the `[/{param}]` bracket syntax.

Parity harness went from **69 problems to 3** after this fix; the remaining 3 are the intentional `/docs`
→ Swagger UI redirect and a pre-existing `users.php` parser bug.

### Phase F complete: test suite at Lumen baseline

The PHPUnit suite (migrated from Lumen's API) now runs **280 tests / 2673 assertions with exactly 1
failure** — the same time-dependent `SeasonControllerTest` test that failed on the original Lumen
baseline. Key migration details for the test layer:

- `tests/TestCase` extends Laravel's base (not Lumen's), `createApplication()` bootstraps via the
  console kernel, and a `$response` + `assertStatus()` compat layer keeps the Lumen-era helpers working.
- `Factory::new()` resolves via the container so contextual bindings (media descriptor) apply; Laravel
  12 uses `new static` which cannot inject interface constructor args.
- `setUpTraits()` override calls `beginDatabaseTransaction()` / `runScoutFlush()` explicitly (Laravel's
  convention expects `begin` + trait-short-name, which doesn't match our trait methods).
- The `mongodb` connection is purged after pointing config at the isolated `jikan_tests` DB, otherwise
  truncations hit the production DB (this wiped seeded data during one test run).
- Text indexes from the squash migration are recreated after truncate (the test DB is never migrated).
- Data providers are `static` (PHPUnit 11), `seeStatusCode`→`assertStatus`, `seeJsonStructure`→
  `assertJsonStructure`.
- Bare boolean flags (`?sfw`, `?continuing`) normalise to `null`/`""` and mean `true` — converted in
  `PreparesData`/`ContextualBooleanCast` *before* validation (casts run after validation).
- `MediaSearchCommand` uses `PreparesData` so empty `start_date`/`end_date` are ignored.

### Known upstream blockers (not migration issues)

- **`jikan-me/jikan` anime/manga parsers break against current MAL markup.** MAL now serves the
  opening/ending theme sections as a JS widget (a `<div>` containing a `<script>`, no `<table>`), but
  `AnimeParser::getOpeningThemes()/getEndingThemes()` (and the manga equivalents) select
  `div.theme-songs.../table/tr` → "current node list is empty" → `ParserException` → `500`. This makes
  `indexer:anime`, `indexer:manga`, `indexer:anime-current-season` and `indexer:anime-schedule` fail,
  and `/v4/anime/{id}` 500 for any scraped id. Producers/magazines/genres parse fine (different
  parsers). Fix belongs upstream in `jikan-me/jikan` (or a fork).
- `/v4/users/recentlyonline` calls `jikan`'s `users.php` scrape which also fails against current MAL
  markup (pre-existing).
- Seeding status: genres ✓, producers in progress (~25 ids/s over 932), magazines via `indexer:common`,
  anime/manga blocked by the parser issue above.
