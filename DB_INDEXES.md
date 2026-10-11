# MongoDB Index Audit — jikan-rest

Branch: `laravel-12` · Audited: 2026-10-11 · Read-only (`mongosh`, no writes, no test suite)

Migration source: single squash migration `database/migrations/2022_12_04_210448_squash.php`.

Databases audited:
- `jikan` — the live/prod database
- `jikan_tests` — test database (never runs migrations; `SyntheticMongoDbTransaction::ensureSearchIndexes()` recreates the text indexes after each truncate)

Index dump command used:

```js
db.getCollectionNames().forEach(n => {
  print(n + ':');
  db.getCollection(n).getIndexes().forEach(i => printjson(i));
});
```

Truths established up front:
- The `jikan` DB is effectively fresh — `anime`, `manga`, `characters`, `people`, `users`, `clubs` have **0 documents** (only `magazines` (1,498) and the small genre/theme/failover collections hold data). The full index set exists regardless.
- **`jikan` DB: 0 indexes missing** vs the migration. All 134 migration-defined indexes are present.
- **`jikan_tests` DB: 118 non-text indexes missing** vs the migration (only the 16 `search` text indexes exist). This is *expected* — tests never run migrations, they only recreate text indexes after truncate.
- One genuine bug found: the test-harness text-index recreation for `characters` and `people` has the field sets **swapped** (see below).

---

## 1. Per-collection status

Legend: ✅ present · ❌ missing · ⚠️ different

### Main collections

| Collection | Migration defines (squash migration) | `jikan` DB | `jikan_tests` DB | Notes |
|---|---|---|---|---|
| `jobs` | `queue, reserved_at` (unnamed → `queue_1_reserved_at_1`) | ✅ | ❌ | tests: no `jobs` indexes |
| `jobs_failed` | (none) | ✅ n/a | ✅ n/a | migration defines no indexes |
| `anime` | `request_hash`(U), `mal_id`(U), `aired`, `aired.from`, `aired.to`, `airing`, `demographics.mal_id`, `explicit_genres.mal_id`, `genres.mal_id`, `licensors.mal_id`, `producers.mal_id`, `studios.mal_id`, `themes.mal_id`, `episodes`, `members`, `favorites`, `popularity`, `rank`, `rating`, `score`, `scored_by`, `status`, `type`, `source`, `title`, `title_english`, `title_japanese`, `title_synonyms`, `search` (text: `title` 50 / `title_japanese` 5) | ✅ all 29 | ⚠️ only `search`; all 28 non-text missing (expected) | prod: 6 column-level indexes auto-named `members_1`, `favorites_1`, `popularity_1`, `rank_1`, `score_1`, `scored_by_1` (see §1.1) |
| `manga` | same shape as `anime` but `published*`/`publishing`/`authors.mal_id`/`serializations.mal_id`/`chapters`/`volumes` (27 total) | ✅ all | ⚠️ only `search` (weights correct) | prod: `members_1`…`scored_by_1` auto-named like anime |
| `people` | `request_hash`(U), `mal_id`(U), `birthday`, `member_favorites`, `name`, `given_name`, `family_name`, `alternate_names`, `search` (text: `name` 50 / `given_name` 5 / `family_name` 5) | ✅ all | ⚠️ `search` present but **wrong fields** — `{name, name_kanji}` instead of `{name, given_name, family_name}` | bug §1.2; prod: `birthday_1`, `given_name_1`, `family_name_1` auto-named |
| `characters` | `request_hash`(U), `mal_id`(U), `birthday`, `member_favorites`, `name`, `name_kanji`, `nicknames`, `search` (text: `name` 50 / `name_kanji` 5) | ✅ all | ⚠️ `search` present but **wrong fields** — `{name, given_name, family_name}` instead of `{name, name_kanji}` | bug §1.2; prod: `birthday_1`, `name_kanji_1`, `nicknames_1` auto-named |
| `magazines` | `mal_id`(U), `count`, `name`, `search`(name:text) | ✅ all 4 | ✅ `search` only (expected; non-text missing) | |
| `clubs` | `request_hash`(U), `mal_id`(U), `name`, `members`, `category`, `created`, `access`, `search`(name:text) | ✅ all | ✅ `search` only (expected) | prod names match migration exactly |
| `producers` | `mal_id`(U), `count`, `name`, `titles`, `established`, `favorites`, `search`(name:text) | ✅ all | ✅ `search` only (expected) | |
| `users` | `request_hash`(U), `mal_id`(U), `internal_username`(U), `joined`, `birthday`, `last_online`, `gender`, `location`, `search`(`internal_username`:text) | ✅ all | ✅ `search` only (expected) | prod: `joined_1`, `birthday_1`, `last_online_1` auto-named |

### Genre / theme collections (`genres_anime`, `demographics_anime`, `explicit_genres_anime`, `themes_anime`, `genres_manga`, `explicit_genres_manga`, `demographics_manga`, `themes_manga`)

| Collection | Migration defines | `jikan` DB | `jikan_tests` DB |
|---|---|---|---|
| all 8 | `mal_id`(U), `count`, `name`, `search`(name:text) | ✅ all 4 each (32 total) | ⚠️ `search` only per collection (expected; non-text missing) |

Extra collections present in the DBs that the migration does **not** create (`anime_episode`, `anime_news`, `characters_pictures`, `users_history`, `season_archive`, `watch`, `failovers_meta`, etc.) carry only the default `_id_` index. Out of scope for this audit.

### 1.1 Cosmetic finding (prod `jikan`): column-level `->index('name')` names not honored

Where the migration declares an explicit index name **on a column definition** (`$table->integer('members')->index('members');` etc.), the MongoDB schema compiler ignored the requested name and generated `field_1`:

| Migration writes | Live index name |
|---|---|
| `anime`/`manga` `members`, `favorites`, `popularity`, `rank`, `score`, `scored_by` | `members_1`, `favorites_1`, `popularity_1`, `rank_1`, `score_1`, `scored_by_1` |
| `people` `birthday`, `given_name`, `family_name` | `birthday_1`, `given_name_1`, `family_name_1` |
| `characters` `birthday`, `name_kanji`, `nicknames` | `birthday_1`, `name_kanji_1`, `nicknames_1` |
| `users` `joined`, `birthday`, `last_online` | `joined_1`, `birthday_1`, `last_online_1` |

(21 indexes total.) Key path and index type are identical — **name-only difference, harmless**. Blueprint-level `$table->index('field', 'name')` and `$table->unique(...)` names *are* honored exactly.

### 1.2 Bug: `characters`/`people` text indexes swapped in the test harness

`SyntheticMongoDbTransaction::ensureSearchIndexes()` (`app/Testing/SyntheticMongoDbTransaction.php:116-156`, specifically `:127-128`) recreates text indexes after each truncate with the field sets crossed:

```php
'characters' => ['fields' => ['name' => 'text', 'given_name' => 'text', 'family_name' => 'text']], // ← people's fields
'people'     => ['fields' => ['name' => 'text', 'name_kanji' => 'text']],                         // ← characters' fields
```

Migration reality (`..._squash.php`):
- `characters` → `name (50), name_kanji (5)`
- `people` → `name (50), given_name (5), family_name (5)`

Observed in `jikan_tests`:
- `characters.search` weights `{family_name:1, given_name:1, name:1}`
- `people.search` weights `{name:1, name_kanji:1}`

Effect: in tests, `$text` search on characters matches `given_name`/`family_name` (fields that don't exist on characters) and loses `name_kanji`; people search matches `name_kanji` instead of the given/family name fields. The prod `jikan` DB text indexes are correct.

---

## 2. Schedule query — actual query shape (with code paths)

### Entry point
`app/Features/QueryAnimeSchedulesHandler.php`
- `:29` — `$this->repository->getCurrentlyAiring($request->filter)`
- `:33` — `$results = $results->filter($requestParams);` → `scopeFilter` (`app/Filters/FilterQueryString.php:20`), inherited via `JikanApiModel` (`app/JikanApiModel.php:14`)
- `:35-40` — `paginate(...)`

### Base query — `DefaultAnimeRepository::getCurrentlyAiring()`
`app/Repositories/DefaultAnimeRepository.php:88-112`

```php
$this->queryable(true)
    ->orderBy("members")                                  // :98  (ascending sort)
    ->where("type", AnimeTypeEnum::tv()->label)           // :99  type = "TV"
    ->where("status", AnimeStatusEnum::airing()->label);  // :100 status = "Currently Airing"

if ($filter->isWeekDay()) {
    $queryable->where("broadcast", "like", "{$filter->label}%");   // :104  prefix regex, e.g. /^Monday/
} else {
    $queryable->where("broadcast", $filter->label);                // :107  equality ("Not scheduled once per week" | "Unknown")
}
```

### Post-handler filters applied on top (always for default params)
`app/Concerns/MediaFilters.php`
- `filterByUnapproved` (`:88-91`): default `unapproved=false` (`HasUnapprovedParameter.php:25`) → **always adds `approved = true`**
- `filterBySfw` (`:81-86`): default `sfw=false` (`HasSfwParameter.php:24`) → **no-op by default**; if used, adds `demographics.mal_id !=`, `genres.mal_id !=`, `rating != "Rx"` (`Anime::scopeExceptItemsWithAdultRating`, `app/Anime.php:220-227`)
- `filterByKids` (`:93-101`): default unset → **no-op**; if used, `demographics.mal_id` equality/not-equals (`app/Anime.php:230-241`)

**Most common resulting Mongo predicate (no query params):**

```
{ type: "TV", status: "Currently Airing", approved: true }
  [+ broadcast: /^Day/ | "Not scheduled once per week" | "Unknown" when ?filter=…]
sort: { members: 1 }
```

> Note: the boolean `airing` field is **not** used by the schedule handler. Indexing the prompt example `(airing, broadcast, type)` would not match this query.

### Related season / top queries using the same fields

| Query | Code | Predicate | Sort |
|---|---|---|---|
| Top airing | `getTopAiringItems` `DefaultAnimeRepository.php:27-32` | `airing = true` | `score` desc |
| Top upcoming | `getTopUpcomingItems` `:34-39` | `status = "Not yet aired"` | `members` desc |
| Season | `getItemsBySeason` `:114-224` | `aired.from` `$gte/$lte` range (`:124-127`), `premiered` equality/null branches (`:136-209`), `aired.string` `$nin`, optional `type` (`:217-219`), continuing-items carry-overs with `aired.to`/`episodes`/`airing=true` | `members` desc |
| Upcoming season | `getUpcomingSeasonItems` `:226-237` | `status = "Not yet aired"` (+ optional `type`) | `members` desc |

### Data-shape caveat worth verifying once data exists
`Anime::setBroadcastAttribute` (`app/Anime.php:113-116`) always stores `broadcast` as a sub-document `{day, time, timezone, string}`. The schedule filters query `broadcast` with string equality / prefix-regex (`DefaultAnimeRepository.php:104,107`); against a stored sub-document those predicates match nothing. If the production dataset follows the setter's shape, the broadcast filter branches never match — confirm against populated data before relying on this index shape (does not affect the index proposal, but affects whether the `broadcast` key pays off).

---

## 3. Proposed compound indexes (PROPOSAL ONLY — not applied)

### P1 — Schedule: `{ status: 1, type: 1, approved: 1, broadcast: 1, members: 1 }`
Serves `getCurrentlyAiring` (`DefaultAnimeRepository.php:88-112`) in *all* variants:
- every request filters `status = "Currently Airing"`, `type = "TV"`, `approved = true` (the last via `filterByUnapproved`, `MediaFilters.php:90`) — three leading equality keys narrow to the schedule set immediately;
- the optional `broadcast` key covers both filter modes: exact equality (`:107`) and the prefix regex `/^Day…/` (`:104`, which uses a B-tree index bound),
- trailing `members` lets MongoDB satisfy `orderBy("members")` (`:98`) without an in-memory sort for the common (no broadcast) case.

### P2 — Season: `{ aired.from: 1, aired.to: 1, airing: 1, members: -1 }`
Serves `getItemsBySeason` (`DefaultAnimeRepository.php:114-224`):
- the base (non-`premiered`) branch filters a range on `aired.from` (`:124-127`) — leading key;
- `aired.to` assists the `includeContinuingItems` carry-over branches (`:151-162`, `aired.to` null/`$gte` range with `airing = true`);
- `airing` helps the continuing-show `$or` branches (`:151-208`);
- `members` desc trailing key aligns with `orderBy('members', 'desc')` (`:223`).

### P3 — Top airing: `{ airing: 1, score: -1 }`
Serves `getTopAiringItems` (`DefaultAnimeRepository.php:27-32`) — `airing = true` equality + `orderByScore()` (`:239-244`) descending sort, fully index-covered (add a leading `approved` only if the unapproval post-filter proves hot).

Companion (not part of the 3, for priority): top / upcoming-season queries filter `status = "Not yet aired"` and sort by `members` desc (`getTopUpcomingItems` `:34-39`, `getUpcomingSeasonItems` `:226-237`) → `{ status: 1, type: 1, members: -1 }`; the existing `status` and `members_1` single-field indexes already provide a baseline.

---

## 4. How to apply (do NOT run / do — for the team)

A new migration creating exactly these indexes:

```php
Schema::table('anime', function (Blueprint $table) {
    $table->index(['status' => 1, 'type' => 1, 'approved' => 1, 'broadcast' => 1, 'members' => 1], 'schedule');
    $table->index(['aired.from' => 1, 'aired.to' => 1, 'airing' => 1, 'members' => -1], 'season');
    $table->index(['airing' => 1, 'score' => -1], 'top_airing');
});
```

Each index creation on a populated collection: `db.anime.createIndex({...}, {name: "..."})` (or `ensureIndexes` via a migration). Run against `jikan` and re-verify with the §1 dump command. This file is intentionally **not** the migration — creating it was out of scope for this read-only audit.

---

## 5. Summary

- **`jikan` DB: 0 missing indexes** — the full migration index set (134) is present. 21 indexes only differ by auto-generated names (`members_1` vs `members`, etc.) — cosmetic.
- **`jikan_tests` DB: 118 of 118 non-text migration indexes missing** — expected (tests never run migrations; only the 16 text `search` indexes are recreated post-truncate).
- **Bug:** `characters`/`people` `search` text indexes are swapped in `SyntheticMongoDbTransaction::ensureSearchIndexes()` (`app/Testing/SyntheticMongoDbTransaction.php:127-128`).
- **Schedule query** filters `{status:"Currently Airing", type:"TV", approved:true}` (+ optional `broadcast` equality/prefix-regex), sorted by `members` — it does **not** use the boolean `airing` field.
- **Proposed compound indexes**: `P1 {status, type, approved, broadcast, members}` (schedule), `P2 {aired.from, aired.to, airing, members:-1}` (season), `P3 {airing, score:-1}` (top airing).
---

## Resolution (2026-10-11)

- **Characters/people text-index swap in the test DB** — fixed in
  `app/Testing/SyntheticMongoDbTransaction::ensureSearchIndexes()`: `characters`
  now recreates `{name, name_kanji}` (weights 50/5) and `people` `{name, given_name,
  family_name}` (weights 50/5/5), matching the squash migration. Verified live in
  `jikan_tests`.
- `ensureSearchIndexes()` hardened to **drop-then-create** the `search` index per
  collection, so a stale/older index definition can never silently survive
  (truncate doesn't drop indexes here and a conflicting `createIndex` was swallowed).
  Verified with an injected wrong index that self-healed.
