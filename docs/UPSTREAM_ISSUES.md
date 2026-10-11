# Upstream Issues (parking lot)

> **RESOLVED 2026-10-12** — jikan **v5.0.0-beta** shipped and was adopted into the fork
> (`jikan-me/jikan:^5.0@beta`): **UP-01…UP-05 are fixed by v5** (verified live — Cowboy Bebop
> incl. themes, Monster w/ status, characters, people, and `users/recentlyonline`) and **UP-06
> is moot** (v5 dropped Goutte; the local shim + composer `replace` were removed). This file is
> kept as the historical record.
>
> Prior note — we were **NOT targetING these at the moment** locally; this file existed so
> upstream `jikan-me/*` defects that block or degrade this fork stay documented
> and searchable, separate from our local remediation work.
>
> **UP-01…05 were filed upstream on 2026-10-11** (links in the table) with full
> diagnosis/repro. UP-06 is already tracked upstream, so it is not re-filed.
>
> - Target repos: [jikan-me/jikan](https://github.com/jikan-me/jikan) (the PHP
>   scraper/parser library) and [jikan-me/jikan-rest](https://github.com/jikan-me/jikan-rest).
> - Every item below predates/independently affects the Laravel migration and was
>   re-verified against the current fork on **Laravel 13.35.0, PHP 8.4.26, current
>   live MAL markup (2026-10)**.
> - Cross-refs: `PR_CHANGELOG.md` §9, `AUDIT.md`, `audit-phase2.md`.

| ID | Repo | Endpoints / commands affected | Symptom (exact) | Root cause | Status |
|---|---|---|---|---|---|
| UP-01 | jikan-me/jikan | `/v4/anime/{id}` + subs, `indexer:anime`, `indexer:anime-current-season`, `indexer:anime-schedule` | `ParserException` — "Failed to parse 'https://myanimelist.net/anime/{id}/'" | **Confirmed.** MAL now serves the opening/ending-theme section as a JS widget (no `<table>`); `AnimeParser::getOpeningThemes()/getEndingThemes()` select `div.theme-songs ... table/tr` → empty node list | **Resolved — v5.0.0-beta** (theme parser null-safe, verified live 2026-10-12) |
| UP-02 | jikan-me/jikan | `/v4/manga/{id}` + subs, `indexer:manga` | `TypeError` — "Cannot assign null to property Jikan\Model\Manga\Manga::$status of type string" (`src/Model/Manga/Manga.php:207`) | Typed-property strictness on PHP 8.x; MAL's current manga page yields a null `status` the model refuses | **Resolved — v5.0.0-beta** (`?string $status`, verified live 2026-10-12) |
| UP-03 | jikan-me/jikan | `/v4/characters/{id}` + subs | `ParserException` — "Failed to parse '.../character/{id}'" | MAL character-page markup change; exact parser method not yet isolated | **Resolved — v5.0.0-beta** (null-safe parser, verified live 2026-10-12) |
| UP-04 | jikan-me/jikan | `/v4/people/{id}` + subs | `ParserException` — "Failed to parse '.../people/{id}'" | MAL people-page markup change; exact parser method not yet isolated | **Resolved — v5.0.0-beta** (null-safe parser, verified live 2026-10-12) |
| UP-05 | jikan-me/jikan | `/v4/users/recentlyonline` | `ParserException` — "Failed to parse 'https://myanimelist.net/users.php'" | Pre-existing parser failure against `users.php`; not migration-introduced | **Resolved — v5.0.0-beta** (endpoint live-verified 200, 2026-10-12) |
| UP-06 | jikan-me/jikan | whole library on Symfony ≥ 7 | Composer cannot install `jikan-me/jikan` v4 with modern Symfony | v4 requires `fabpot/goutte` (abandoned; caps `symfony/mime` ≤ 6). **Working around locally** with `app/Compat/Goutte/Client.php` + `"replace": {"fabpot/goutte": "4.0.3"}` (see `PR_CHANGELOG.md` §2) | **Resolved — v5 drops Goutte; shim (`app/Compat/Goutte/`) + composer `replace` removed 2026-10-12** |

---

## Detail

### UP-01 — Anime parser breaks on current MAL theme markup (confirmed)
- **Symptom:** every `/v4/anime/{id}` request (and the anime indexers) → HTTP 500
  `ParserException`, `error: Failed to parse 'https://myanimelist.net/anime/{id}/'`.
- **Root cause (confirmed in `vendor/jikan-me/jikan`):** `AnimeParser::getOpeningThemes()`
  and `getEndingThemes()` select `div.theme-songs ... / table / tr` and call node
  methods → "current node list is empty". MAL no longer ships the OP/ED table in
  static HTML (JS widget instead).
- **Impact here:** blocks anime seeding (`indexer:anime*`) and all anime endpoints;
  parity for anime endpoints is pinned at the documented blocked state; `schedules`,
  `seasons_*` and `top_*` stay empty because they depend on anime docs.
- **Candidate upstream fix:** update the theme-songs selectors for current MAL markup
  (or treat a missing table as "no themes" instead of throwing).

### UP-02 — Manga model fails with PHP 8.4 typed-property TypeError
- **Symptom:** `/v4/manga/{id}` → HTTP 500 `Exception` (not `ParserException`):
  `Cannot assign null to property Jikan\Model\Manga\Manga::$status of type string`
  at `src/Model/Manga/Manga.php:207`.
- **Impact here:** blocks `indexer:manga` and all manga endpoints. Note the manga
  failure mode differs from anime — it's a model-assignment error, not a parse error.
- **Candidate upstream fix:** nullable/coerced `$status` property on the model (and
  review sibling nullable string properties under PHP 8 strict typing).

### UP-03 / UP-04 — Characters & people parsers fail on current MAL pages
- **Symptom:** `/v4/characters/{id}` and `/v4/people/{id}` (and sub-resources) →
  500 `ParserException` ("Failed to parse '.../character/{id}'" / "'.../people/{id}'").
- **Root cause:** not yet isolated to a specific parser method (would need a debug
  scrape against current MAL markup on a real network-enabled run).
- **Candidate upstream fix:** isolate the failing section parser(s) against current
  markup and update selectors (same class of work as UP-01).

### UP-05 — `users/recentlyonline` parser failure (pre-existing)
- **Symptom:** `/v4/users/recentlyonline` → 500 `ParserException` "Failed to parse
  'https://myanimelist.net/users.php'". Present on the original Lumen baseline too
  (documented `users.php` scrape failure).

### UP-06 — Goutte dependency caps Symfony (worked around locally)
- **Symptom:** `jikan-me/jikan` v4 requires the abandoned `fabpot/goutte`, which pins
  Symfony ≤ 6 and cannot install alongside Laravel 13 (Symfony 8).
- **Local workaround (active):** `app/Compat/Goutte/Client.php` (HttpBrowser wrapper)
  + `"replace": {"fabpot/goutte": "4.0.3"}` in `composer.json`.
- **Long-term upstream fix:** `jikan-me/jikan` v5 (drops Goutte / uses BrowserKit
  directly). Until then the shim stays.

---

## How to re-check once upstream fixes land
1. Boot a fresh server, then expect `200` on: `/v4/anime/1`, `/v4/manga/1`,
   `/v4/characters/1`, `/v4/people/1`, `/v4/users/recentlyonline`.
2. Re-run (`tools/parity/snapshot.php` → `compare.php`) — the anime/manga/characters/
   people endpoints should fall back into the byte-match set.
3. Re-seed `indexer:genres`, `indexer:common`, `indexer:anime`, `indexer:manga`
   (once parseable) to restore full local data.
4. If UP-06 lands first (jikan v5), drop the `fabpot/goutte` `replace` + the
   `app/Compat/Goutte/` shim.