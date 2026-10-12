# Recipe — add an endpoint (end-to-end)

Steps for adding a new read-only v4 endpoint, in order. Follow to the letter;
each step has a verify gate. Works for agents and humans alike.

## 0. Pick the shape
- Detail of an existing resource → sub-resource of the controller's `{id}` group.
- New list/search → its own route under `/v4`.
Keep the v4 contract style: `{status, type, message}` errors, `pagination` +
`data` envelope for lists, per-endpoint cache TTL.

## 1. Route
`routes/web.v4.php` (inside the v4 group, string actions):

```php
$router->get('/my-resource', [
    'uses' => 'MyController@index',
]);
```

Gate: `tools/dev/php artisan route:list` shows it; `backend-suite audit route-coverage` green.

## 2. DTO (request)
`app/Dto/MyLookupCommand.php` — extends the Data base; add query params as
typed properties (this is also the fingerprint source):

```php
final class MyLookupCommand extends Data implements DataRequest
{
    public function __construct(
        public readonly int $id,
    ) {}
}
```

Gate: `php -l` clean; binds in `AppServiceProvider::registerRequestHandlers()`.

## 3. Handler
`app/Features/MyLookupHandler.php` — `RequestHandlerWithScraperCache` (for
scraper cache) or a plain handler; call the service/repository, return the
Resource/`CachedData`.

Gate: unit-test the handler with a fake service.

## 4. Service / Repository
Put logic in `app/Services/` (repositories in `app/Repositories/`). Respect the
layer boundaries audit (`app/Http/**` must not import `app/Services/**`).

## 5. Resource
`app/Http/Resources/V4/MyResource.php` — extends `JsonResource`; add the
component schema as a **class-level `#[OA\Schema(...)]` attribute**
(`schema: 'my_resource'`, properties as `#[OA\Property(...)]`).

Gate: component appears after regeneration.

## 6. Controller + `@OA`
`app/Http/Controllers/V4DB/MyController.php` — method + `#[OA\Get(...)]`
attribute on the method: `path`, `operationId`, `summary`, `description`,
`parameters` (path/query with `schema`), `responses` (200 with
`new OA\JsonContent(ref: '#/components/schemas/my_resource')` + standard 400).

Gate: `backend-suite audit endpoint-parity` green; spec regenerates.

## 7. Tests
- Unit: handler/service logic with fakes.
- Integration: HTTP-level test (fixture/seeded cache; no live MAL — the test
  harness throws `CustomTestException` on network calls).

Gate: full suite green.

## 8. Regenerate + verify generated artifacts
```bash
tools/dev/php artisan l5-swagger:generate
tools/dev/php artisan endpoints:write          # regen docs/ai/*
tools/dev/php artisan endpoints:write --check  # gate: exit 0
```

Gate: `git diff --exit-code` shows only your new/edited files, never stale
spec/manifest diffs.

## 9. Wrap
- Changelog entry (`docs/PR_CHANGELOG.md`).
- If it's a high-volume/get-by-id path, add a per-endpoint TTL in
  `config/jikan.php` (`per_endpoint_cache_ttl`) — `CacheTtlMapTest` enforces
  completeness.
- Parity: run `tools/parity/snapshot.php` + compare if the endpoint is in the
  parity set.

**Definition of done:** suite green · generate exit 0 · endpoints:check exit 0 ·
endpoint-parity/route-coverage/toolchain-gates exit 0 · generated artifacts
unmodified.