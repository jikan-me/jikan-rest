# jikan-rest — Phase-2 Audit: Laravel idiom, Symfony integration, architecture

**Audited:** `/Users/irfan/Developer/jikan-rest` (fork of `jikan-me/jikan-rest`)
**Runtime:** Laravel `13.35.0` · PHP `8.4.26` · `mongodb/laravel-mongodb 5.11.0` · Octane `2.21.0` (RoadRunner) · Scout `11.9.0` (native Typesense) · spatie/laravel-data 4.x · Symfony resolved to **8.1.x** (framework) with scraper components on **7.4.x**
**Scope:** service container quality, Symfony package correctness, architecture (mediator/handlers/repos, events, jobs, middleware, routing, Mongo, spatie/data) — deeper than AUDIT.md and explicitly complementary to it.
**Method:** static reading only. No app code was modified; nothing was run. Line numbers reflect the working tree (with the in-flight P0 batch; small shifts are possible).
**Overlap note:** Where a finding extends an AUDIT.md item I say so explicitly and add new depth (`file:line` or a sharper mechanism); I do not restate audit-1 wholesale. **NEW** = not surfaced by audit-1.

---

## Executive summary

The P0 env()→config, rate-limiting, and APP_KEY-bypass rework is visibly **landing in parallel** — `bootstrap/app.php`, `config/jikan.php`, `routes/api.php:57` (`throttle:v4`), `AppServiceProvider::boot()` `RateLimiter::for('v4')` (AppServiceProvider:76-84), and `config('jikan.*')` reads in `Handler` are all done or in progress, and only one raw `env()` call remains in `app/` (`PsrWorkerErrorListener.php:18`). The remaining `Env::get` calls (`HttpHelper.php:33`, `ScraperCacheTtl.php:11`) are outside the P0 batch but only feed dead code (see §3.4).

Phase-2's genuinely **new** high-value findings:

### Top 5 NEW findings (for orchestrator triage)
1. **`MicroCaching`'s request-route introspection is dead code on Laravel** → the `NO_CACHING` bypass silently stopped working (migration regression). `MicroCaching.php:42-48` does `isset($request->route()[1]['uses'])`. `Illuminate\Routing\Route` implements **no** `ArrayAccess` (verified: `vendor/laravel/framework/src/Illuminate/Routing/Route.php` has no `offset*` methods and no interfaces), so that block is always skipped. Under Lumen `route()` returned `[0=>params, 1=>['uses'…]]`; under Laravel it returns a `Route` object. Net effect: `/v4/random/*` and `/v4/insights/*` are no longer excluded from micro-caching and get served identically from the micro-cache for `MICROCACHING_EXPIRE` (5 s in `.env.dist`) — every random request returns the *same* entry. **P1 correctness, upstream PR.**
2. **`UpdateCacheJob`/`UpdateDatabaseJob` are broken at multiple levels and are dead code.** Both call `app('GuzzleClient')` (`UpdateCacheJob.php:59`, `UpdateDatabaseJob.php:51`) but the container only binds `'HttpClient'` (Symfony) — `'GuzzleClient'` was never registered anywhere (`AppServiceProvider.php:105-107`) → `BindingResolutionException` if ever run. `UpdateDatabaseJob` additionally declares `protected $requestUri;` (`:27`) but never assigns it (constructor `:36-46`), then uses it at `:54` → `null` concatenated into the URL. They are never dispatched anywhere in the codebase (grep: only self-references) — so **latent**, but any attempt to revive the old warm-cache flow breaks. Also the whole pattern is wrong-headed regardless (see §3.4). **P1, local (delete or rewrite).**
3. **Heartbeat fail-pruning is a no-op and the score math is off.** `SourceHeartbeatListener::getRecentFails()` (`:132-142`) and `SourceHeartbeatProvider::getHeartbeatScore()` (`:49-54`) both do `foreach ($fails as $fail) { … unset($fail); }` — `$fail` is a **value copy**, so nothing is removed; the condition is also inverted relative to the comment (`$fail[0] >= time()-range` removes *recent* entries, `unset` being ineffective saves it from doing real damage). Only the trailing `array_slice($fails, 0 - MAX_STORE)` (`:145-147`/`:57-58`) caps growth, and it keeps the **most recent** entries, so the `SOURCE_BAD_HEALTH_RANGE` time-window semantics are simply never applied. **P1/P2, local + upstream.**
4. **`IncrementalIndexer` re-processes the same MAL id for the whole run.** The loop is `for ($i = $index; $i <= $idCount-1; $i++)` but reads `$id = $ids[$index]` (`IncrementalIndexer.php:118-149`) — `$index` is constant → one id is fetched `$idCount` times; `_resume.save` is also written as `$index` (`:148`) so `--resume` never advances. Separately the "new ids" diff is inverted: `array_diff($existingIds[$t], $newIds[$t])` (`:79`) yields ids *removed* from `$newIds` (the intent is `array_diff($newIds[$t], $existingIds[$t])`, newly added ids). **P1, local + upstream (indexers are a scheduled, documented feature).**
5. **`/v4/insights/trends` ignores the `trend` parameter.** `InsightsController::trends()` validates `trend ∈ {anime,manga,people,characters}` (`:91`) but the query never applies it — the `->where('url','regexp',…)` filter is commented out (`:100-109`, `:112`), and the first aggregation stage groups by `url`+`timestamp` (`:117-123`) so all four trend values return the same data; `total` is the raw row count from `DB::table('insights')->count()` (`:149`), the wrong dimension for a URL-level aggregation. **P1 when `INSIGHTS=true`, local.**

### Other notable new items
- Symfony **mixed 7.x / 8.x** dependency graph (framework on 8.1, scraper stack pinned ^7.0) — works today, fragile tomorrow (§2).
- `config/queue.php` **default queue connection dangles** (`'mongodb'` connector used as a connection name that has no `connections.*` entry) (§3.4).
- Debug `\Log::info` churn left in the hot `/v4/schedules` handler (§3.3).
- `CorsMiddleware` re-wraps every response through `ResponseFactory::make()`, destroying `JsonResponse` semantics, when Laravel 13 already ships `HandleCors` (§2).
- `Handler::render()` string-matches `"Undefined index: url"` (`Handler.php:195`) — PHP 8 rewrote that message to "Undefined array key", so the branch is dead on 8.4 (§2).
- `AuthServiceProvider` (Lumen `viaRequest`, uses `$request->input('api_token')`) and `SearchQueryBuilderProvider` (imports the removed `App\Http\QueryBuilder\…` namespace) are dead files, and neither is registered in `bootstrap/providers.php` (§3.1).
- spatie/laravel-data: `HasRequestFingerprint::fromRequest()` hand-builds a 9-field `CreationContext` — direct coupling to v4 internals that already churned once this migration (§3.7); `PreparesData` and `ContextualBooleanCast` double-handle the bare-flag `""/null → true` case (§3.7).

---

## Findings by area

### 1. Service container quality

This is the app's weak spot per audit-1 §3; phase-2 adds mechanism-level detail.

#### 1.1 Magic-string bindings vs typed contracts
| File:Line | What | NEW? | Assessment |
|---|---|---|---|
| `AppServiceProvider.php:105-107` | `instance('HttpClient'|'JikanParser'|'SerializerV4')` — string-keyed, permanent instances | depth on audit-1 §3 #A | Container services without interface/class keys are untraceable (`app('JikanParser')` has no contract; PHPStan can't verify), and `instance()` instances survive Octane flush (fine here because all three are immutable-ish: a Symfony client, a `MalClient`, and an already-built JMS serializer). But the **16+ consumers** call `app('JikanParser')`/`app('SerializerV4')` directly from static model methods (`Anime.php:245-249`, `Manga.php:146-150`, `Character.php:70-74`, `Person.php:71-75`, `Club.php:75-79`, `Profile.php:49-53`, `Magazine.php:56-60`, `Producers.php:48-52`, `GenreAnime.php:51-54`, `GenreManga.php:50-53`) and 6 indexers (`CommonIndexer.php:56-58`, `AnimeScheduleIndexer.php:51-53`, `CurrentSeasonIndexer.php:53-54`, `GenreIndexer.php:52-53,142-143`, `ProducersIndexer.php:148-149`) — the static `MyAnimeListScraper::scrape()` pattern (`modelClass::scrape($id)` from `DatabaseRepository.php:47-53`) makes the whole scraper layer untestable without stubbing the container. Fix: bind `MalClient`/`Serializer` under their classes and a small `ScraperClient` contract; keep `'JikanParser'` alias only as compatibility. |
| `AppServiceProvider.php:98-113` | `alias(JikanConfig::class, "jikan-config")` + global helper `max_results_per_page()`/`text_match_buckets()` do `app()->make("jikan-config")` (`Support/helpers.php:58-70`) | — | Working; the alias is only needed for the Lumen-era global helpers. Acceptable; consider dropping the helpers in a Laravel-native rewrite. |
| `AppServiceProvider.php:109-114` | `Typesense\Client` + `TypesenseCollectionDescriptor` singletons gated on `scout.driver === "typesense"` | — | OK. `TypesenseCollectionDescriptor` caches per-collection searchable attributes in a **private readonly `$cache`** (`TypesenseCollectionDescriptor.php:14-33`) — immutable per worker, safe under Octane. Binding it only when Typesense is configured is correct. |
| `AppServiceProvider.php:181-343` | contextual `when(DefaultMediator::class)->needs(RequestHandler::class)->give(...)` builds **all ~65 handlers + ~30 `DefaultCachedScraperService` + a `Typesense\Client`-backed search service each** per `Mediator` resolution | depth on audit-1 §3 #6 | Still the largest single construction cost. Compounding detail: the `give()` closure (a) re-runs `getSearchService()` 7× for the search handlers (`:200-202`), each go-around building a fresh `SearchEngineSearchService`/`DefaultScoutSearchService`/`TypeSenseScoutSearchService`; (b) calls `$unitOfWorkInstance->documents($table)` ~30× (`JikanUnitOfWork.php:98-101`) which `new`s a **fresh `DocumentRepository` per call** rather than resolving a container service; (c) only *then* `make`s the handler. Alternative with the least churn: bind each concrete `RequestHandler` normally and give `DefaultMediator` a lazy `['dto-class' => handler-class]` map (built once, static), resolving the one handler on `send()`; or container `when($handler)->needs(X)->give(...)` per handler so `make()` resolves them lazily. |
| `AppServiceProvider.php:171` | `$this->app->bind(Mediator::class, DefaultMediator::class)` (not singleton/scoped) | — | Combined with `octane.php:147` flushing `Mediator`, every request re-runs the closure above. Correct per-request behavior, costly. A `scoped()` binding wouldn't help here; making the handler graph lazy does. |

#### 1.2 bind / singleton / scoped decisions + Octane state
| File:Line | What | NEW? | Assessment |
|---|---|---|---|
| `RepositoryQueryBase.php:10-11,26-47` + `AppServiceProvider.php:156-158` | Repositories are **singletons** and cache a `queryableBuilder`/`searchableBuilder` **inside the singleton**; only `JikanUnitOfWork`, `Mediator`, `SearchService` are flushed (`octane.php:142-149`) | depth on audit-1 §3 #7 / Exec #4 | Concrete contamination path: search with `SCOUT_DRIVER=null` (the **local default**, `config/scout.php:18`) → `MongoSearchService::search()` (`MongoSearchService.php:14-15`) → `query()` → `repository->filter($params)` (`SearchServiceBase.php:22-25` → `RepositoryQuery.php:12-15`) → **cached** `queryable()` (default `createNew=false`). The builder (and every `where`/`orderBy` added by `filterBy*`/`OrderbyClause`) persists on the singleton across requests of the same Octane worker. `createEntity()` (`DatabaseRepository.php:16-19`, used by `TypeSenseScoutSearchService::middleware()` `:64`) also pinches the cached builder. Fix: make `filter()`/`where()`/`createEntity()` use `queryable(true)`, or flush the 8 `*Repository` contracts + the two genres repos in `octane.php` flush (cheapest). |
| `CacheOptions.php:10-24` + `EndpointCacheTtlMiddleware.php:22-31` | Request-scoped TTL singleton; middleware sets TTL, calls `$next`, resets — **no reset when the inner handler throws** | audit-1 §3 #8 | Confirm severity: on exception the stale per-endpoint TTL stays in `CacheOptions` for the worker's next request; affects `CachedData::from()` (`CachedData.php:40`) and the `ResponseJikanCacheFlags` headers. A `try/finally` reset or a per-request `CacheOptions` flush fixes it. |
| `AppServiceProvider.php:160-163` | `AnimeGenresRepository`/`MangaGenresRepository`/`UnitOfWork` singletons | — | Genre repos only expose `getX()` (`AnimeGenresRepository.php:22` `queryable()->get()`) — the cached builder **is** executed on the singleton and never reset → under Octane a genre-list request pollutes worker state for the next genre request. Same class of fix as above. |
| `AppServiceProvider.php:85-88,107` | `singleton(ExceptionHandler::class, Handler::class)` and the three permanent `instance()` bindings | — | Accepted design; the exception-handler singleton is what makes `withExceptions()` look empty (`bootstrap/app.php:40-42`). Audit-1 §1 flags moving rendering into `withExceptions()` — endorse, but note it's a behavior-preserving refactor, not a bug. |

#### 1.3 Facade usage in domain code — where DI is warranted, where acceptable
- **Acceptable / idiomatic:** `DB::table()` in controllers that do direct Mongo fallback lookups (`SearchController`, `CharacterController`, `ClubController`, `InsightsController`) — thin controllers, aligned with Laravel convention.
- **Would be better as DI (testability):** `Log::` inside models (`Anime.php:13`) and the debug logging inside `QueryAnimeSchedulesHandler` (§3.3); `Storage::` in `SourceHeartbeatListener`/`SourceHeartbeatProvider` (statics — see §3.2); `Cache::` in `MicroCaching` (`MicroCaching.php:9,66,80`) — fine as middleware; `app('redis')` string key in `UpdateCacheJob.php:76` (use the `Redis` facade/`Contracts\Redis\Factory`).
- **Acceptable:** `DB::`/`Storage::` inside the indexers (CLI tools, not unit-tested).

---

### 2. Symfony package usage correctness (explicitly requested)

#### 2.1 Installed major is mixed — verify intent
Composer pins `symfony/browser-kit`, `css-selector`, `dom-crawler`, `http-client` at `^7.0` (`composer.json:28-32`), but Laravel 13.35 resolves `symfony/http-foundation`, `http-kernel`, `console`, `mime`, `process` at **8.1.x** (verified in `composer.lock`: http-foundation v8.1.8, http-kernel v8.1.8, console v8.1.8, mime v8.1.7, process v8.1.7, error-handler v8.1.8; browser-kit v7.4.17, dom-crawler v7.4.17, css-selector v7.4.18, http-client v7.4.20). The scraper stack is internally consistent (browser-kit 7.4's `HttpBrowser` ⇄ http-client 7.4 ⇄ dom-crawler 7.4, and `jikan-me/jikan` v4's `GoutteWrapper` extends our `Goutte\Client` shim which extends `HttpBrowser` — all 7.x). Framework + scraper meet only through `Symfony\Contracts\HttpClient\HttpClientInterface` (http-client-contracts 3.7 — contract-stable across majors), so it **works today**:
- `Response::$statusTexts` still public in http-foundation 8.1.8 (`vendor/symfony/http-foundation/Response.php:127`) → `Handler.php:213` is fine, but `$statusTexts[$code]` on a non-standard `HttpException` code yields an undefined-key warning — prefer `Response::$statusTexts[$code] ?? 'unknown status'`.
- `HttpBrowser` constructor signature unchanged (`vendor/symfony/browser-kit/HttpBrowser.php:34-40`), so the shim's same-signature constructor is valid.

Why it matters: the four `^7.0` pins keep the scraper stack permanently on a different major than the app server, so any dependency that later enforces a uniform major (e.g. an upgraded `jikan` v5, a Scout/Octane patch touching `symfony/http-client`) can break resolution or silently dual-install. **Action:** relax those four to `^7.4 || ^8.0` (or `^8.0`) once `jikan` v5 (Goutte-free) lands, and add a composer `conflict` note. Target: upstream.

#### 2.2 Goutte shim correctness
`app/Compat/Goutte/Client.php` is a faithful v4.0.3 re-implementation (verified constructor + no added behavior). Caveats worth recording:
- The `replace {"fabpot/goutte": "4.0.3"}` (`composer.json:37-39`) satisfies `jikan-me/jikan` v4's `fabpot/goutte: ^4.0` requirement (`vendor/jikan-me/jikan/composer.json` requires only that) — the shim is the *sole* bridge, meaning the migration's Schedule "check symfony/dom-crawler usage in the scraper layer" resolves to: no direct app usage of `Crawler` outside the shim (grep: `app/` only references `CookieJar/History/HttpBrowser` in `Compat/Goutte/Client.php` and the two timeout exception classes in `Handler.php:17-19`); all `Crawler`/`CssSelector` consumption lives in `vendor/jikan-me/jikan` and exercises exactly the 7.4 dom-crawler API. **Acceptable bridge; keep.**
- The `@deprecated` stub docblock is honest. When `jikan` v5 drops Goutte, delete the shim + the `replace` block.

#### 2.3 Re-implementations of framework/symfony-provided behavior
- **`CorsMiddleware`** (`app/Http/Middleware/CorsMiddleware.php:14-52`) extends Laravel's own `HandleCors` but then wraps *every* matched response through `ResponseFactory::make($response->getContent(), $status, $headers)` (`:21-26,51`). That re-wraps a `JsonResponse` into a plain `Response` — dropping `original`, `$encodingOptions`, `setCallback` state that later code (error middleware, tests, `->original['error']` checks like `HttpHelper::hasError`/`SourceHeartbeatListener`-style checks) relies on. Laravel 13's stock `HandleCors` + `config/cors.php` already does all of this (and `fruitcake/php-cors` is present in the lock). Gated by `env('CORS_MIDDLEWARE')` (default off in `.env.dist`), so low blast radius. **Fix:** delete the subclass and append `HandleCors::class` (or keep the opt-in env but wrap only the preflight path). Local + upstream.
- **`public/index.php:35`** `ob_start('ob_gzhandler')` — gzip shim re-implementing proxy behavior, inactive under Octane, double-gzip risk behind a CDN (audit-1 §6 flagged; kept here as an HTTP-layer item: remove).
- **Manual HTTP self-calls** (`UpdateCacheJob.php:62`, `UpdateDatabaseJob.php:54`, `AnimeIndexer.php:114`, `IncrementalIndexer.php:132`, `CurrentSeasonIndexer` via URL) — the app could call the scraper service in-process instead of HTTP-GET'ing its own endpoint (§3.4).
- **`HttpResponse.php:8-34`** static helpers building JSON with `\json_encode(...)` + `response(...)` — dead (only an unused import in `CommonIndexer.php:6`). `json_encode`+`response()` is fine, but Laravel's `response()->json()` is the idiom; file is unused.

#### 2.4 Symfony/HttPFoundation idioms in controllers/middleware
- Controllers type-hint `App\Dto\*Command` (spatie Data) — Laravel's container resolves them from the request (`vendor/spatie/laravel-data/src/LaravelDataServiceProvider.php:49-58`), then the models are built via the DTO pipeline. Clean.
- `$request->input()` vs `->get()`: search only finds `->get()` in `InsightsController.php:29-30,77-79` (query params — correct Symfony/Laravel idiom for input; `->get()` there is `Illuminate\Http\Request::input()`'s alias and works). `AuthServiceProvider.php:34-35` uses `$request->input('api_token')` — fine idiom, dead code (see §3.1).
- `Response::$statusTexts` (§2.1) and the message-string exception matching (`Handler.php:195`, below) are the only fragile Symfony-adjacent idioms.
- `Laravel\Lumen`-era `$request->route()[1]` array access in `MicroCaching.php:42` — **Laravel-incompatible** (top finding #1).

#### 2.5 Symfony-version-sensitive exception handling
- `Handler.php:195` `$e->getMessage() === "Undefined index: url"` — PHP 8 changed this engine warning to "Undefined array key \"url\"" (and it is no longer a recoverable message on many paths), so this branch is effectively dead on PHP 8.4. It exists to catch MAL-parser array-access failures; better: catch the concrete exception class or check `$e instanceof \ErrorException` with a code. (audit-1 §6 covers the redaction side; NEW: the branch is dead for message-format reasons.)
- `Handler.php:213` `Response::$statusTexts[$e->getStatusCode()]` — works on Symfony 8 (verified), add the `?? 'unknown status'` guard.

---

### 3. Architecture

#### 3.1 Feature/handler/mediator + repository + DTO layer
- Overall the pattern is **consistent and faithfully applied** (thin controllers, per-request DTO validation, handler per command, repo abstraction) — audit-1's "intentional" verdict stands. Phase-2 specifics:
  - `DefaultMediator` (`DefaultMediator.php:17-20`) has no concept of a missing DTO at bind time and returns a 500 JSON from a **service** for unknown handlers (`:29-33`) — a `send()` that type-errors (interface declares `@return T`) never `throw`s; a controller bug yields a "Programmer error" JSON instead of a `500`/`HttpException`. Prefer `throw new \LogicException(...)` and let the exception handler render. NEW, style/correctness-adjacent.
  - `UnitOfWork` (`JikanUnitOfWork.php`) is a **locator-object over already-singleton repositories** — every handler graph ultimately resolves the same 8 singletons; the value is only naming. Combined with `documents()` `new`-ing a `DocumentRepository` per call (above), the UoW doesn't de-duplicate anything under Octane. Acceptable architecturally; just know it's a façade.
  - `DefaultCachedScraperService::find()` (`DefaultCachedScraperService.php:62-78`) does DB-get → (scrape) → DB-get again (`:166-171`), i.e. 2–3 queries + possible live scrape per cold lookup — audit-1 §5 (N+1-ish) covered; add: the scrape step (`repository->scrape($id)`) runs `modelClass::scrape()` and, on `HttpHelper::hasError`, `abort(404)` — meaning **a `UserSearch` cold miss that hits `findByKey` throws from `DocumentRepository::scrape()` ("Not supported")** — BUT every `findByKey` consumer uses the `users()` (model-backed) repo (`AppServiceProvider.php:293-298` → `DefaultUserRepository`), so `DocumentRepository::scrape` can only throw if a future handler maps a `findByKey` list to a `documents()` repo. Flag for the refactor, not a live bug.
  - `CachedData` duplication-of-logic: `isExpired()`/`expiry()`/`lastModified()` (`CachedData.php:53-100`) reimplement Mongo `expiresAt` semantics in PHP with `modify`d `Carbon::now()` comparisons — the DB rows also carry `expiresAt` (`DatabaseRepository.php:70`, `Job`-side) that is never consulted by `CachedData`; two expiry clocks on the same record. Acceptable (parity), but the redundant `expiresAt` writes (`UpdateDatabaseJob.php:70`) should be reconciled when the jobs are rewritten.

#### 3.2 Events/listeners
- `SourceHeartbeatEvent` doubles as a **constant bag and an event** (`SourceHeartbeatEvent.php:15-16` `BAD_HEALTH/GOOD_HEALTH`) — the constants belong on a `Health` enum or the provider; minor.
- `SourceHeartbeatMonitor` fires `GOOD_HEALTH/200` on **every request** (`SourceHeartbeatMonitor.php:20`); `BAD_HEALTH` is only fired from `Handler::render` (`Handler.php:151,172,184,196`). So the "score" is "good requests ∕ all requests", and the listener's `insertFail()` records both — the naming ("`insertFail`") is misleading but the data model is coherent.
- `SourceHeartbeatListener` constructor **does I/O and side-effects at construction time** (`SourceHeartbeatListener.php:25-43`): builds a Monolog `StreamHandler` (`:28`), reads the lock timestamp, and (when a lock exists) calls `attemptDisableFailover()` which re-reads+parses `failovers.json` (`:112-119`), and in `handle()` does `Storage::put('failovers.json', ...)` per request (`:64-73`) — **read-modify-write with no lock and no atomic rename** → multi-worker Octane races (audit-1 §7/P1#5 covered the I/O; NEW: constructor-time side effects + non-atomic write + the broken prune in §3.4/top #3).
- `use App\Events\ExampleEvent;` is imported (`SourceHeartbeatListener.php:5`) and the `handle()` docblock says `@param ExampleEvent` (`:48`) while the signature is `SourceHeartbeatEvent` — dead import + wrong docblock (audit-1 §12 mentions the import; here precise file:line).
- The event system itself is used idiomatically (provider `$listen` map, `event()` helper) — the problem is what the listener does, not the mechanism.

#### 3.3 Middleware responsibilities
- `MicroCaching` decides and executes caching (bypass, fetch, store) — good cohesion except the dead `route()` introspection (top #1). It stores with `Cache::add(...)` (only-if-not-exists) and serves from cache before warm-up; upstream parity behavior. One asymmetry: `config/jikan.php:24` sets `'microcaching_expire' => env('MICROCACHING_EXPIRE', 5)` while `MicroCaching.php:85` still reads `env('MICROCACHING_EXPIRE', 60)` directly — after `config:cache` these two defaults diverge (5 vs 60) because `env()` goes null → default 60. The in-flight P0 env→config pass should absorb `MicroCaching.php:85` to kill the split-brain.
- `EndpointCacheTtlMiddleware` mutates a **singleton** (`CacheOptions`) — acceptable for Octane (per-request) *only if* reset is exception-safe (§1.2).
- `SourceHeartbeatMonitor` — pure event trigger; correctly separated from scoring.
- `Insights::terminate()` (`Insights.php:32-50`) — full-scan `DELETE` (range) + insert per request (audit-1 §4); add: `terminate()` still runs under Octane per-request, and the `timestamp` column is a raw unix int compared against `time()` — fine for Mongo, just confirming no TTL index/expireAfterSeconds is used (a Mongo TTL index on `timestamp` would remove the per-request DELETE entirely).

#### 3.4 Jobs — architectural critique of the self-HTTP bootstrap (explicitly requested)
`UpdateCacheJob`/`UpdateDatabaseJob` are a **dead, broken pattern**:
1. **Breakage:** `app('GuzzleClient')` unbound (top #2); `UpdateDatabaseJob::$requestUri` never assigned (`:27` vs `:54`); both import `GuzzleHttp\Client` but never use it; `synchronized `solved` elsewhere. Both call `env('APP_URL')`/`env('APP_KEY')` (now under the P0 batch) and `sleep((int) env('QUEUE_DELAY_PER_JOB', 5))` (`:78`/`:76`).
2. **Pattern critique:** making the app HTTP-GET itself to "refresh" the Mongo cache means (a) the worker must reach `APP_URL` over the network (breaks in private/ingress-only deploys), (b) it needs the `auth: APP_KEY` header to skip middleware — the very pattern P0 #5 is removing, (c) under **Octane a saturated worker pool can deadlock** (the HTTP request needs a worker to serve it), (d) the TTL keys written (`request:type:uriHash`) are **never read** — `MicroCaching` reads `sha1(...)` keys (`MicroCaching.php:63-66`), and the scraper cache lives in Mongo keyed by `request_hash` — so even a fixed job would write to a key nothing consumes.
3. **Alternative:** dispatch a closure job (`dispatch(function () { app(CachedScraperService::class)->warm($uri); })`) or a dedicated `CacheWarmCommand`, or drive warming from the app request path itself (prefetch after a scraped response is generated) — no HTTP hop, no auth header, no sleep-held worker. `UpdateDatabaseJob`'s `DB::table()->update(['expiresAt' => ...])` (`:66-74`) is the only part with a real purpose (bumping expiry without re-scraping) — fold that into `DefaultCachedScraperService` instead.
4. **Config:** `config/queue.php:18` defaults `QUEUE_CONNECTION` to `'mongodb'`, but there is **no `connections.mongodb` entry** (the defined `connections.*` are `sync|database|beanstalkd|sqs|redis`; the Mongo-backed queue *connector* is a driver used on the `database` connection, `:37-44`). `mongodb/laravel-mongodb` registers a `'mongodb'` **connector** (`vendor/mongodb/laravel-mongodb/src/MongoDBServiceProvider.php:105`), not a connection — so `QUEUE_CONNECTION=mongodb` resolves null config and the manager fatals. `.env.dist` sets `QUEUE_CONNECTION=database` (correct); the shipped *default* is the trap. Also `queue.php:40` hardcodes a `mongodb+srv://` DSN using `DB_USERNAME`/`DB_PASSWORD` — a different DSN shape than the DB connection — for the Mongo queue. Fix the default to `database` and drop the bespoke DSN (the `database` queue inherits `connection: mongodb`). **NEW.**

#### 3.5 HttpHelper, fingerprints, cache-key design
- `HttpHelper::requestType()` (`HttpHelper.php:20-28`) indexes `$request->segments()[1]` **before** guarding — a `\v1`-stub or segment-less request yields an "Undefined array key" warning + `null` type; MicroCaching guards segments first (`MicroCaching.php:56-61`), `requestAPIVersion` `segment(1)` is 1-based (`:36-39`) — inconsistent indexing conventions across the same helper (0-based `segments()[1]` vs 1-based `segment(1)`). Works, worth a guard + comment. (audit-1 §4 mentions; NEW: the 0/1-based inconsistency + warning on `/v1`.)
- Cache-key design: three key families — Mongo `request_hash` (raw fingerprint), `Cache::` microkeys (`sha1(fingerprint)`), and Job keys (raw fingerprint `request:type:uriHash`, unconsumed). The sha1 hop (MicroCaching) exists to shave Redis key length — fine, but document that micro-cache hits **strip `meta`/`links`** (`MicroCaching.php:69-70`) so cached search responses differ from fresh ones by more than timestamps (parity note).

#### 3.6 Routing
- Audit-1 §1 covers: group `namespace` + string actions, duplicate `/v4/producers`, env/config-gated user-list routes at load time. Phase-2 additions:
  - `routes/web.v4.php:285` — the **first** `/producers/{id}` (inside the `producers` group, `:276-289`) has **no `->where('id','[0-9]+')`**, while the later standalone group at `:295-312` adds it. Because Laravel matches in registration order, `/v4/producers/full` and `/v4/producers/external` bind `id='full'/'external'` to `ProducerController@main` before the constrained routes run — resulting in a 500-class response (int-cast/parse) instead of a clean 404. Pre-existing; cheap to delete the unconstrained twin (the full group at `:295` supersedes it).
  - `routes/api.php:41` `/docs` → 301 redirect is the only non-`v4` route besides the stubs — fine.
  - `routes/web.v4.php:380` uses `config('jikan.disable_user_lists')` now (P0 batch) — still **route-load-time** gating, so `route:cache`/`php artisan route:list` output varies by env; register always and 404 in the controller instead (audit-1 noted; confirm still an item after the env→config fix).

#### 3.7 spatie/laravel-data specifics
- **Query-parameter DTO pipeline** — the app overrides `Data::pipeline()` (`MapsRouteParameters.php:15-25`) to insert a custom `MapRouteParametersDataPipe` between `MapPropertiesDataPipe` and validation. That is idiomatic spatie v4 and works; the custom pipe correctly maps `$request->route(...)` (`MapRouteParametersDataPipe.php:19-27`).
- **`HasRequestFingerprint::fromRequest()`** (`HasRequestFingerprint.php:20-39`) hand-constructs a 9-field `CreationContext` and calls `DataFromSomethingResolver::execute()` directly — this is the method that had to be re-worked once already for v4 (PR_CHANGELOG §4) and re-couples the app to internal constructor args. `disableMagicalCreation: true` (`:28`) is what stops spatie's own `from*` magic-method discovery (which would otherwise find `fromRequest` again); if a future spatie release renames/reorders any `CreationContext` arg, every DTO breaks again. Cleaner: a documented spatie `fromRequest` custom creation method (return `static::from($request)->withFingerprint(...)`) or a `DataPipe` that stamps the fingerprint as the last pipe.
- **Bare-flag handling is duplicated**: `PreparesData::prepareForPipeline()` (`PreparesData.php:49-51`) and `ContextualBooleanCast::cast()` (`ContextualBooleanCast.php:14-16`) both map `""|null → true`. Harmless (cast runs later and sees already-true), but the two should be consolidated—pick `prepareForPipeline` (it must win because casts run after validation, and validation of a bare `?sfw` fails `BooleanType` before the cast ever runs — the `PreparesData` path is the load-bearing one).
- **Trait composition duplication**: `HasSfwParameter` `use PreparesData` (`HasSfwParameter.php:22`) while consumers re-list `PreparesData` directly (`MediaSearchCommand.php:27`, `QueryAnimeSchedulesCommand.php:27`) — legal (PHP dedups identical trait inclusion) but confusing; drop the direct re-uses.
- `PreparesData.php:29` calls `app(DataConfig::class)` inside a *static* factory method — service-locator-in-DTO; acceptable, but note it makes `prepareForPipeline` hard to unit test in isolation.

#### 3.8 MongoDB / laravel-mongodb specifics
- `scopeRandom`'s `raw(... $collection->aggregate(['$sample'...]))` (`JikanApiModel.php:27-63`) — correct Mongo idiom; `$sample` is nondeterministic by design (audit-1 §5 OK). Note `QueryRandomAnimeHandler::handle()` (`QueryRandomAnimeHandler.php:27-29`) calls `Anime::query()->random(1, ...)` directly, bypassing the `Repository::random` abstraction — minor.
- `whereIntegerInRaw(mal_id, ...)` in `JikanSearchable::queryScoutModelsByIds` (`JikanSearchable.php:54-60`) — correct for Mongo int fields; the `(int)$v` cast (`:59`) is applied on both branches so non-int key types would silently coerce (audit-1 §5 noted; no change).
- `DefaultAnimeRepository::getItemsBySeason` uses `$dateDiff` with a **`new UTCDateTime($from)`** (`DefaultAnimeRepository.php:180,196`) — `UTCDateTime` from a `Carbon` object is the correct BSON conversion, and the `$dateDiff : dateFromString` on a string `aired.from` is a pre-existing date-modeling trade-off (dates stored as strings by the JMS serializer). The comment at `:168` documents the Mongo ≥ 5.0 dependency — worth an explicit server-version check in docs (audit-1 §7 mentions index checks).
- Mongo TTL: insights and heartbeat would benefit from `expireAfterSeconds` indexes instead of app-level deletes (§3.3) — opportunity, not bug.
- `database.php:3-10` DSN still built from env at config load (audit-1 §2); P0 batch has it in scope. Note `DB_ADMIN` is used as the **path (default DB)** segment of the DSN, not as `authSource` — so MongoDB auth actually falls back to the `admin` DB implicitly; if that's not the deployment's auth DB, connections may fail once real auth is enabled. Verify against the production URI. NEW detail.

---

### 4. Upstream-PR calculus (what maintainers of jikan-rest will accept/reject)

**MUST fix before an upstream PR (correctness, will be refused as-is):**
- `MicroCaching` `route()[1]` dead code (top #1) — it's a silent behavioral regression; a maintainer will spot it on first `/random` test.
- Jobs: dead + broken §3.4 — delete or rewrite before the PR (upstream currently ships these; leftover dead code referencing `GuzzleClient` is a red flag).
- Heartbeat prune no-op + score math (top #3) — upstream has this transitively via the old code; fixing both files is small and reviewable.
- `IncrementalIndexer` (top #4).
- `InsightsController::trends()` dead `trend` filter (top #5).

**Should fix locally first, or as a staged follow-up PR (behavior-preserving):**
- Handler-graph laziness (P1), repo/cache-options Octane flush (P1), micro-cache/environmental TTL consolidation (P2), mixed-Symfony constraint relax (P2, note as "chore: composer constraints"), `config/queue.php` default fix (P2), `CorsMiddleware` deletion (P2), `AuthServiceProvider`/`SearchQueryBuilderProvider` dead-file deletion, `QueryAnimeSchedulesHandler` debug logs, `Handler` message-string branch → concrete exception type.

**Will likely be accepted as-is (parity-preserving, intentional):**
- String-action routes + group `namespace` (still supported; conversion is a separate "modernize routes" PR).
- Goutte `replace` shim (documented risk, accepted until jikan v5).
- spatie Data layer, `$text` Mongo fallback, `request_hash` Mongo scraper cache, heartbeat JSON file (with the bug fixes above), GitHub `report_url` behavior.

---

## Prioritized action plan (phase-2 delta — supersets audit-1 §plan)

### P0 — none new (audit-1's Docker/config-cache/rate-limit/redaction set remains; rate limiting + config are now landing in the P0 batch)

### P1
| # | Action | Ref | Effort | Target |
|---|---|---|---|---|
| 1 | Fix `MicroCaching` route introspection (use `$request->route()` `Route` API or the `routeName`-derived `getRouteName`); restore `NO_CACHING` bypass; add a test that `/random` returns different payloads across calls | `MicroCaching.php:42-48` | ½ d | **Upstream PR** |
| 2 | Delete or rewrite `UpdateCacheJob`/`UpdateDatabaseJob` (fold expiry-bump into `DefaultCachedScraperService`; cache-warm via in-process closure job/command) | §3.4, `Jobs/*` | ½–1 d | Local + upstream |
| 3 | Octane state: make `RepositoryQueryBase::filter()/where()/createEntity()` use `queryable(true)` (or flush the 8 repo contracts + `AnimeGenresRepository`/`MangaGenresRepository`/`CacheOptions`); `EndpoointCacheTtlMiddleware` reset on exception | §1.2 | ½–1 d | Local + upstream |
| 4 | Lazy handler map for `DefaultMediator` (DTO class → handler class, resolve on `send`) | `AppServiceProvider.php:181-343` | 1–1½ d | Upstream PR |
| 5 | Fix heartbeat prune (by-ref iteration or `array_filter` + correct `<`/`>=` boundary), atomic `Storage::put` (tmp+rename), constructor-side-effect-free listener | `SourceHeartbeatListener.php:28-43,121-150`; `SourceHeartbeatProvider.php:39-71` | ½ d | Upstream PR |
| 6 | Fix `IncrementalIndexer` loop index + diff direction; add a unit test | `IncrementalIndexer.php:79,118-149` | ½ d | Local + upstream |
| 7 | `InsightsController::trends`: apply the URL regexp / derive `total` from the aggregate | `InsightsController.php:91-136,149` | ½ d | Local |

### P2
| # | Action | Ref | Effort | Target |
|---|---|---|---|---|
| 8 | Remove debug `\Log::info` from `/v4/schedules` (and from `Anime.php` where unused) | `QueryAnimeSchedulesHandler.php:26,30,34,41` | 15 m | Local |
| 9 | Composer: relax `symfony/browser-kit|css-selector|dom-crawler|http-client` to `^7.4 \|\| ^8.0` (or unify on 8) so the graph stops mixing majors; remark when jikan v5 lands | `composer.json:28-32` | 15 m | Upstream PR |
| 10 | `config/queue.php`: default `QUEUE_CONNECTION` → `database`; drop the bespoke `mongodb+srv` DSN on the `database` queue connection | `config/queue.php:18,37-44` | 15 m | Local |
| 11 | Delete dead files/imports: `AuthServiceProvider` (unregistered, Lumen API), `SearchQueryBuilderProvider` (imports removed namespace), `HttpResponse` usage, `Controller::mutation()`/`$request`/`$response`, `SerializerFactory::convertMalUrlv2`, `SourceHeartbeatListener` dead `ExampleEvent` import/docblock | §3.1 | ½ d | Local |
| 12 | `Handler.php:195` branch → concrete exception type (message-string matching is dead on PHP 8.4); add `?? 'unknown status'` guard at `Handler.php:213` | `Handler.php:195,213` | ½ d | Upstream PR |
| 13 | Delete `CorsMiddleware` subclass; use Laravel `HandleCors`; remove `ob_gzhandler` from `public/index.php` | `CorsMiddleware.php`; `public/index.php:35` | ½ d | Upstream PR |
| 14 | Micro-cache TTL split-brain: single source (`config/jikan.php`), absorb `MicroCaching.php:85` into the P0 config pass; re-sync `per_endpoint_cache_ttl` stale keys (`characters_staff`, `seiyuu`, `mainAnime`/`mainManga`, `MagazineController@resource`) — audit-1 §2 | `config/jikan.php:42-112`; `MicroCaching.php:85` | ½ d | Local |
| 15 | Route hygiene: delete unconstrained `/producers/{id}` in the first group; always-register user-list routes (move gating into `UserController`) | `routes/web.v4.php:285,380` | ½ d | Upstream PR |
| 16 | Guard `HttpHelper::requestType()` against short segment arrays; align notes on 0-based `segments()[1]` vs 1-based `segment(1)` | `HttpHelper.php:20-28,36-39` | 15 m | Local |

---

## Intentional / acceptable (do not "fix" blindly)
- **Feature/handler/mediator + repository + DTO layer** — deliberate design; consistent; the only phase-2 critiques are construction cost and Octane state, not the pattern itself.
- **`request_hash`-keyed Mongo scraper cache with `createdAt/modifiedAt/expiresAt`** — design; the unused `expiresAt` writes are reconciled in the job cleanup.
- **`$text` Mongo search as the SCOUT_DRIVER-unset fallback** — design; audit-1 §P1#9 (filters dropped) stands.
- **Goutte `replace` shim + mismatched Symfony majors** — accepted bridge until jikan v5 (relax constraints, don't force).
- **Heartbeat JSON-file scoring** — kept for parity; fix the pruning/math and writes, not the transport.
- **GitHub `report_url` on errors** — intentional public-API behavior; keep the shape.
- **Parity preservation** — route/response shape intentionally preserved byte-for-byte; don't "modernize" responses while claiming parity.
---

## Resolution status (living; updated as fixes land)

Status at time of writing: the P0 correctness batch (env()→config centralization, rate limiting, error redaction, APP_KEY→CACHE_BYPASS_TOKEN) is in progress/in the same commit cycle as this audit. New phase-2 findings (MicroCaching route-introspection regression, dead/broken update jobs, heartbeat pruning no-op, IncrementalIndexer resume bug, insights/trends) are triaged in the backlog.

### Phase-2 P1 batch (resolved)
- `MicroCaching` NO_CACHING regression — fixed (route introspection via `getActionName()`), verified against the live route table. ✅
- `UpdateCacheJob` / `UpdateDatabaseJob` — deleted (unreferenced, unbound client, wrong pattern); cache-warm redesign tracked. ✅
- Heartbeat pruning no-op + score overflow — fixed via `SourceHeartbeatProvider::pruneFails()`; denominator uses real event count. ✅
- Remaining phase-2 items (mind you): Symfony 7/8 pin alignment (`^7.4 || ^8`), `config/queue.php` mongodb default, `insights/trends` filter, `IncrementalIndexer` resume/id-diff, `CorsMiddleware` response wrapping, `pub`/dead files removal — still open.
- `SyntheticMongoDbTransaction` production-DB truncation hazard — **fixed** (`b4c206d`): `beginDatabaseTransaction()` now refuses to truncate unless the resolved database is the isolated test DB. Sentinel-verified across a full suite run. ✅
- `IncrementalIndexer` resume/id-diff — **fixed**: loop now advances `$i` (`$ids[$i]`), the id-diff direction corrected (fetch *new* ids), resume saves `$i` per iteration, and the success/failed bookkeeping matches intent (success on success, failed on error, 404 skip). Also fixed a `${var}` deprecation. ✅
- `/v4/insights/*` feature — **removed by product decision** (unused): controller, recording middleware, resources, routes, config/env keys and its parity entry all deleted.
- `CorsMiddleware` response re-wrap — **removed**: custom middleware deleted; CORS now handled by Laravel's default `HandleCors` + `config/cors.php` (the re-wrap was Lumen-era cruft). ✅
- Heartbeat listener transport (§3.2) — **moved off flat files to MongoDB** (`d1df28f`): per-request `failovers.json`/lock-file read-modify-write → atomic `insertOne` into the `failovers` collection, lock + last-downtime as `failovers_meta` docs; constructor-side file I/O and `getRecentFails()`/`insertFail()` file reads removed; recency window/cap still enforced by `SourceHeartbeatProvider::pruneFails()` on the in-memory arrays. `/`+`/v4` heartbeat SHAPE unchanged (live + Octane verified). ✅
- Swagger UI / l5-swagger — **removed (product decision)**: provider, `config/l5-swagger.php`, `app/Swagger.php`, `/docs` redirect, parity entries and env keys deleted; `composer remove darkaonline/l5-swagger zircote/swagger-php`. `@OA\` annotations left as inert comments. ✅
