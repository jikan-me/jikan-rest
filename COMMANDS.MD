# Artisan Commands

Artisan commands are run in the root of the project directory where the `artisan` file is present.
For an entire list of commands, you can run `php artisan list`

## Index
- [Serve](#serve)
- [Queue](#queue)
    - [Clear](#queue-clear)
- [Cache](#cache)
    - [Remove](#cache-remove)
- [Indexers](#indexers)
    - [Anime](#indexer-anime)
    - [Manga](#indexer-manga)
    - [Incremental](#indexer-incremental)
    - [Items](#indexer-items)
    - [Harvest](#indexer-harvest)
    - [Genres](#indexer-genres)
    - [Common](#indexer-common)
    - [Producers](#indexer-producers)
    - [Anime sweep](#indexer-anime-sweep)
    - [Manga sweep](#indexer-manga-sweep)
    - [Anime schedule](#indexer-anime-schedule)
    - [Current season](#indexer-anime-current-season)
- [Scout](#scout)
    - [Import](#scout-import)
    - [Flush](#scout-flush)
- [Endpoints](#endpoints)

## Commands

### Serve
Command: `serve`
Example: `php artisan serve`

Serve the application on the PHP development server

### Queue

#### Queue: Clear
Command: `queue:clear`

Example: `php artisan queue:clear`

This will empty any jobs that are currently in the queue (e.g. queued Scout imports when `SCOUT_QUEUE=true`).

### Cache

#### Cache: Remove
Command: `cache:remove {key}`

Example: `php artisan cache:remove request:anime:d6092f2422f084452c84555f17c7ba047e6998d3`

This will remove the cache of a specific request. You can retrieve any request's hash key by visiting the request.

In v2-v3, the request hash key is defined in the JSON response body as `request_hash`.

In v3.4+, the request is defined in the response headers as `X-Request-Hash` as well.

In v4+, the request is defined only in the response header.

The cache store is chosen with `CACHE_DRIVER` (or `CACHE_STORE`) in `.env` (default `file`); see `docs/UPGRADING.md`.

### Indexers

All indexers write into MongoDB (`anime`, `manga`, `characters`, `people`, `producers`,
`magazines`, genres/themes/demographics). They do **not** fill the Typesense search
index — that is a separate `scout:import` step (see below).

Exit codes: `0` success, non-zero when the run cannot start (unknown family, missing
id cache).

| Command | What it indexes |
|---|---|
| `indexer:anime` | every anime id from the purarue cache |
| `indexer:manga` | every manga id from the purarue cache |
| `indexer:incremental anime\|manga` | only ids new since the last incremental run |
| `indexer:items anime\|manga\|character\|people` | one id family, from `--source` |
| `indexer:harvest` | harvest character/people ids from stored anime/manga (phase A) |
| `indexer:genres` | anime + manga genres, themes, demographics, explicit genres |
| `indexer:common` | magazines (list endpoint) |
| `indexer:producers` | every producer id from the purarue cache |
| `indexer:anime-sweep` / `indexer:manga-sweep` | deletes entries no longer on MAL |
| `indexer:anime-schedule` | refreshes currently airing anime |
| `indexer:anime-current-season` | refreshes the current season |

#### Indexer: Anime
Since v4 uses MongoDB as a means to index cache on some endpoints, having a built cache is important since it
works best for endpoints like search or top.

`Indexer:Anime` uses [https://github.com/purarue/mal-id-cache](https://github.com/purarue/mal-id-cache) to fetch available MAL IDs and indexes them.

This function only needs to be run once. Any entry's cache updating will automatically be taken care of if it's expired, and a client makes a request for that entry.

⚠ This is strictly for performance and experience and providing better search functionality. Don't build your own anime database as that's against MyAnimeList's Terms of Service.

Command:
```
indexer:anime
    {--failed : Run only entries that failed to index last time}
    {--resume : Resume from the last position}
    {--reverse : Start from the end of the array}
    {--index=0 : Start from a specific index}
    {--delay=3 : Set a delay between requests}
```

Example: `indexer:anime --reverse --delay=5 --failed`

This translates to running entries that previously failed to index or update, in reverse, with a delay of 5 seconds between each request.

There is no `--limit`: a run covers the whole id cache. Failed ids are recorded in
`storage/app/indexer/indexer_anime.failed`; use `--failed` to retry just those.

#### Indexer: Manga
Since v4 uses MongoDB as a means to index cache on some endpoints, having a built cache is important since it
works best for endpoints like search or top.

`Indexer:Manga` uses [https://github.com/purarue/mal-id-cache](https://github.com/purarue/mal-id-cache) to fetch available MAL IDs and indexes them.

This function only needs to be run once. Any entry's cache updating will automatically be taken care of if it's expired, and a client makes a request for that entry.

⚠ This is strictly for performance and experience and providing better search functionality. Don't build your own anime database as that's against MyAnimeList's Terms of Service.

Command:
```
indexer:manga
    {--failed : Run only entries that failed to index last time}
    {--resume : Resume from the last position}
    {--reverse : Start from the end of the array}
    {--index=0 : Start from a specific index}
    {--delay=3 : Set a delay between requests}
```

Example: `indexer:manga`

This simply translates to running the indexer without any additional configuration.

#### Indexer: Incremental
Incrementally indexes media entries from MAL.
This command will compare the latest version of MAL ids from the [mal_id_cache](https://github.com/purarue/mal-id-cache)
github repository and compares it with the downloaded ids from the previous run. If no ids found from the previous run, a full indexing session is started.

Command:
```
indexer:incremental {mediaType*}
    {--failed : Run only entries that failed to index last time}
    {--resume : Resume from the last position}
    {--delay=3 : Set a delay between requests}
```

`mediaType` accepts `anime` and `manga`.

#### Indexer: Items
Indexes one MAL id family by scraping each id through the app's **own** endpoint
(`config('app.url') . '/v4/{family}/{id}'`), so the parsed document lands in Mongo the
same way a client request would cache it.

Command:
```
indexer:items {family}
    {--source=auto : auto|harvest|purarue — where to read ids from}
    {--failed : Run only entries that failed to index last time}
    {--resume : Resume from the last position}
    {--reverse : Start from the end of the id list}
    {--index=0 : Start from a specific index}
    {--delay=3 : Seconds between requests}
    {--timeout=30 : Seconds before a single HTTP request is abandoned}
    {--limit=0 : Stop after N entries (0 = all)}
```

- `family`: `anime`, `manga`, `character`, `people`. Clubs have no id cache and are
  populated lazily through user club lists.
- `--source=auto` (default): `character`/`people` read the harvested id sets from
  `indexer:harvest`; `anime`/`manga` read the purarue caches.
- `--source=harvest` / `--source=purarue` force one source.

Example: `indexer:items character --source=harvest --resume --delay=2`

#### Indexer: Harvest
Phase A of the relational character/people indexer: walks the **already stored**
anime/manga collections and harvests live character/person MAL ids from their
sub-resources (`anime/{id}/characters`, `anime/{id}/staff`, `manga/{id}/characters`,
and `manga.authors` straight from the stored document).

Ids are deduped into `storage/app/indexer/harvest_character.json` and
`harvest_people.json`, which `indexer:items character|people --source=harvest` then
reads. See `docs/INDEXER_SCALING.md` for measured costs.

Command:
```
indexer:harvest
    {--kind=all : all|anime|manga}
    {--no-staff : Skip the anime /staff pass (characters + voice actors only)}
    {--failed : Run only entries that failed last time}
    {--resume : Resume from the last position}
    {--reverse : Start from the end of the id list}
    {--index=0 : Start from a specific index}
    {--delay=2 : Seconds between requests}
    {--timeout=30 : Seconds before a single HTTP request is abandoned}
    {--limit=0 : Stop after N entries (0 = all)}
```

Example: `indexer:harvest --kind=anime --no-staff --resume`

#### Indexer: Genres
Indexes anime and manga genres, themes, demographics and explicit genres (the
`/genres/*` catalogues). Entries are updated in place if they already exist.

Command:
```
indexer:genres
```

#### Indexer: Common
Indexes common endpoint metadata from the list endpoints. Today that is
**magazines**; producers are covered by `indexer:producers`.

Command:
```
indexer:common
```

#### Indexer: Producers
Fetches the producer MAL ids from the purarue cache and scrapes each producer
through `/v4/producers/{id}` (same self-scrape pattern as `indexer:anime`).

Command:
```
indexer:producers
    {--failed : Run only entries that failed to index last time}
    {--resume : Resume from the last position}
    {--reverse : Start from the end of the array}
    {--index=0 : Start from a specific index}
    {--delay=3 : Set a delay between requests}
```

#### Indexer: Anime sweep
Deletes anime that no longer exist on MAL: fetches the current MAL id list, compares
it with the local `anime` collection, and removes the difference. Run it after an
index pass so search/top do not advertise dead entries.

Command:
```
indexer:anime-sweep
```

#### Indexer: Manga sweep
Same as the anime sweep, for the `manga` collection.

Command:
```
indexer:manga-sweep
```

#### Indexer: Anime schedule
Refreshes the anime that are currently airing so `/v4/schedules` stays fresh.
The Laravel scheduler (`routes/console.php`) runs it daily at 00:10.

Command:
```
indexer:anime-schedule
```

#### Indexer: Current season
Refreshes the anime in the current season so `/v4/seasons/now` stays fresh.

Command:
```
indexer:anime-current-season
```

### Scout

Search indexing is Laravel Scout. Collections auto-create on import from each
model's `typesenseCollectionSchema()`. These commands only do anything useful with a
running search engine (`SCOUT_DRIVER=typesense` + `TYPESENSE_*`); the default
`SCOUT_DRIVER=null` uses Mongo `$text`.

#### Scout: Import
Command: `scout:import "App\Model"`

Example: `php artisan scout:import "App\Anime"`

Imports (or re-imports) every document of that model into its Typesense collection.
The searchable models are `App\Anime`, `App\Manga`, `App\Character`, `App\Person`,
`App\Club`, `App\GenreAnime`, `App\GenreManga`, `App\Profile`, `App\Producers`,
`App\Magazine`, `App\SearchMetric`.

#### Scout: Flush
Command: `scout:flush "App\Model"`

Example: `php artisan scout:flush "App\Anime"`

Removes every document of that model from its search collection (the collection
schema stays). To rebuild after a schema change, delete the collection itself in
Typesense (`DELETE /collections/<name>`) and import again — there is no
collections-creation command, and collections must not be pre-created by hand.

### Endpoints
Command: `endpoints:write [--check] [--diff]`

Regenerates the committed AI artifacts (`docs/ai/endpoints.json`,
`docs/ai/manifest.json`) from the generated OpenAPI spec, or verifies them:

```bash
php artisan l5-swagger:generate          # spec first
composer endpoints:write                 # write the artifacts
composer endpoints:check                 # exit 1 if they are stale
```

`--check` verifies instead of writing; `--diff` is `--check` plus a printed diff.
