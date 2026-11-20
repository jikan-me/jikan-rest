# Local Setup (macOS, native — no Docker)

This documents how this checkout is set up to run locally on this machine.
It is machine-specific helper documentation from the migration; the general instructions are `docs/SETUP.md` and `README.MD`.

## What is installed

| Component | Version | How |
|-----------|---------|-----|
| PHP       | 8.4.26  | **Homebrew `php@8.4`** + `pecl install mongodb` (2.5.4). Herd Lite's PHP is not used: it bundles `ext-mongodb` 1.20, which cannot be upgraded in place and is too old for the `mongodb/laravel-mongodb` v5 driver. Run PHP via `tools/dev/php`. |
| Composer  | 2.8.12  | Homebrew (already present) |
| MongoDB   | 9.0.2   | `brew install mongodb-community` (managed by `brew services`) |

Docker / Docker Compose are **not** installed, so the repo's `docker-compose.yml`
stack (Mongo + Redis + Typesense) is not used here. The app runs directly with PHP.

## Configuration

- `.env` — copied from `.env.dist` (git-ignored). Changes made:
  - `APP_ENV=local`, `APP_DEBUG=true`, `APP_URL=http://localhost:8000`
  - `APP_KEY` generated via `php artisan key:generate`
  - MongoDB defaults kept (`DB_CONNECTION=mongodb`, `DB_HOST=localhost`, `DB_PORT=27017`, `DB_DATABASE=jikan`)
- `php.dev.ini` — *(obsolete)* project-local PHP config that was used with the
  Lumen-era `php -S` dev server to keep `E_DEPRECATED` notices out of responses.
  Neither the file nor that launch path exists anymore; see "Run it" below.

## Run it

> **Obsolete (2026-11-20).** The `tools/dev/php -c php.dev.ini -S …` command below
> is how the Lumen-era checkout was started. `tools/dev/php` is a gitignored,
> machine-local wrapper (it does not exist on a fresh clone) and `php.dev.ini` is
> no longer in the repo. Use the Octane/Swoole path instead — `script/server`
> (`SERVER=frankenphp|serve` to switch runtime), see `README.MD` and
> `docs/PROVIDERS.md`.

```bash
# current
composer install
script/server                 # Octane/Swoole on :8000

# historical (Lumen-era dev server — do not use)
# tools/dev/php -c php.dev.ini -S 127.0.0.1:8000 -t public/
```

Then:

```bash
curl http://127.0.0.1:8000/            # API info JSON
curl http://127.0.0.1:8000/v4/anime/1  # requires MyAnimeList access (see below)
```

> `php artisan serve` is the no-Swoole fallback (single-threaded). The
> `php.dev.ini` note above no longer applies: the file is not in the repo.

## Known limitations in this environment

1. **No outbound access to MyAnimeList.net.** `curl https://myanimelist.net/...`
   times out (`HTTP 000`) from this machine, so any endpoint that scrapes MAL
   returns:
   ```json
   {"status":500,"type":"UpstreamException","message":"Request to MyAnimeList.net timed out (10 seconds). ..."}
   ```
   Endpoints backed by local data still return `200` (e.g. `/`, `/v4/genres/anime`,
   `/v4/anime?q=...`), just with empty results because the database is not seeded.
2. **Empty database.** Populating it requires the indexers, which also scrape MAL:
   ```bash
   php artisan list | grep indexer   # indexer:genres, indexer:anime, indexer:manga, ...
   ```
   This cannot succeed without MAL connectivity.
3. **Search engine.** Typesense is not installed; search falls
   back to database queries (`SCOUT_DRIVER=null`), which is fine for local use.

## Tests

```bash
php vendor/bin/phpunit --no-coverage   # or: composer phpunit
```

The upstream README notes tests may fail due to MyAnimeList rate limiting
(HTTP 429) — and here they will additionally fail on network access to MAL.
