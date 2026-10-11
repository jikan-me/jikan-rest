# Local Setup (macOS, native — no Docker)

This documents how this checkout is set up to run locally on this machine.
It is **untracked** helper documentation.

## What is installed

| Component | Version | How |
|-----------|---------|-----|
| PHP       | 8.4.26  | **Homebrew `php@8.4`** + `pecl install mongodb` (2.5.4). Herd Lite's PHP is not used: it bundles `ext-mongodb` 1.20, which cannot be upgraded in place and is too old for the Laravel 12 Mongo driver. Run PHP via `tools/dev/php`. |
| Composer  | 2.8.12  | Homebrew (already present) |
| MongoDB   | 9.0.2   | `brew install mongodb-community` (managed by `brew services`) |

Docker / Docker Compose are **not** installed, so the repo's `docker-compose.yml`
stack (Mongo + Redis + Typesense) is not used here. The app runs directly with PHP.

## Configuration

- `.env` — copied from `.env.dist` (git-ignored). Changes made:
  - `APP_ENV=local`, `APP_DEBUG=true`, `APP_URL=http://localhost:8000`
  - `APP_KEY` generated via `php artisan key:generate`
  - MongoDB defaults kept (`DB_CONNECTION=mongodb`, `DB_HOST=localhost`, `DB_PORT=27017`, `DB_DATABASE=jikan`)
- `php.dev.ini` — project-local PHP config used to run the dev server.
  Lumen forces `error_reporting(-1)` when `APP_DEBUG=true`, and the locked
  Laravel 9 / Lumen 9 dependencies emit huge numbers of `E_DEPRECATED` notices
  on PHP 8.4. This ini sets `display_errors=Off` so those notices stay out of
  HTTP responses (they are still logged). Real exceptions are still rendered by
  the framework error handler.

## Run it

```bash
# 1. Make sure MongoDB is running
brew services start mongodb/brew/mongodb-community

# 2. Start the API (from the repo root)
cd ~/Developer/jikan-rest
tools/dev/php -c php.dev.ini -S 127.0.0.1:8000 -t public/
```

Then:

```bash
curl http://127.0.0.1:8000/            # API info JSON
curl http://127.0.0.1:8000/v4/anime/1  # requires MyAnimeList access (see below)
```

> `php artisan serve` also works, but it shells out to the plain PHP binary and
> ignores `-c php.dev.ini`, so deprecation notices leak into responses.

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
3. **Search engine.** Typesense/Elasticsearch are not installed; search falls
   back to database queries (`SCOUT_DRIVER` unset), which is fine for local use.

## Tests

```bash
php vendor/bin/phpunit tests
```

The upstream README notes tests may fail due to MyAnimeList rate limiting
(HTTP 429) — and here they will additionally fail on network access to MAL.
