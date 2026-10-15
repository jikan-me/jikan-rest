# Updater design (proposal)

Status: proposal, no code. Intended to land as `docs/UPDATER.md` and a tracking issue after #614.

## Goal

Anyone running Jikan REST (Docker or bare metal) can move to a newer release with one
well-defined step, without hand-running migrations, reindexing everything, or guessing
what a release changed.

## What an update has to cover

| Layer | Example of what changes |
|---|---|
| Code | new image / `git pull` |
| Dependencies | parser bump (e.g. `jikan-me/jikan` v5.0.0-beta -> rc.1) |
| Stored data | fields added or fixed by the parser (manga relations, `friends_since`, producer fields) leave old Mongo documents stale |
| Search index | Typesense collection schema changes need re-creation and `scout:import` |
| Safety | preflight (PHP >= 8.4.4, ext-mongodb, Typesense reachable), backup, health check, rollback |

## Components

### 1. Version and release channel

- `jikan:version` prints the running version, the PHP/parser versions and the configured channel.
- Channel config: `JIKAN_UPDATE_CHANNEL=stable|beta` (default `stable`). `beta` is the only channel that
  may resolve a pre-release parser, so choosing one is deliberate.
- Version source: GitHub releases for the repo; a `version.json` baked into the image/repo for the running side.

### 2. Update check (read-only)

- `jikan:update --check` compares running vs latest on the channel and exits non-zero when behind.
- Optional field on a status endpoint: `{"version": "...", "channel": "stable", "update_available": true}`.
  Opt-in (`JIKAN_STATUS_EXPOSE_VERSION=true`); off by default so public instances do not advertise their version.
- Optional webhook/log line on a schedule. Never changes anything.

### 3. `jikan:update`

Steps, each idempotent and logged:

1. Preflight: PHP version, extensions, Mongo and Typesense reachable, free disk, no other update running (lock).
2. Optional backup hook (`JIKAN_UPDATE_BACKUP_CMD`, e.g. `mongodump --archive=...`), skipped if unset; a printed restore hint.
3. `artisan down` (maintenance, with a retry-after).
4. `migrate --force`.
5. Run pending **data migrations** (section 4).
6. If a Typesense collection schema hash changed, re-create that collection and re-import.
7. Clear/rebuild caches; `octane:reload`.
8. `artisan up`; health check `/up` and one smoke request. On failure: print what to roll back (code and, if taken, the backup), leave maintenance mode off only after the check passes.

Code delivery stays separate: `git pull`/`composer install` or a new image tag.
`jikan:update` is the part that makes the *state* match the new code.

### 4. Versioned data migrations

A registry, like schema migrations but for stored documents:

- Collection `data_migrations` records which ran, when, and the release.
- Each migration is a class with `id`, `description`, `appliesWhen` (e.g. parser version crossed X) and a strategy:
  - **lazy re-fetch (default):** mark only the affected cached docs as expired (e.g. manga relations, user profiles) so
    they refresh on next read or via the existing indexers. No full reindex.
  - **eager:** run an indexer command for a family (`indexer:items`, `indexer:incremental`) with limits/delay.
- Release notes list the migrations a release carries; `jikan:update --plan` prints them and an estimate without running anything.

### 5. Docker

- Publish tagged images per release/channel.
- `docker-entrypoint.sh` can run `jikan:update --auto` on boot when `JIKAN_AUTO_UPDATE=true` (default false), so
  `docker compose pull && up -d` self-migrates. Watchtower-style tools work on top, at the operator's choice.
- Multiple replicas: the update lock ensures one container migrates; others wait.

## Non-goals

- No default auto-update of public instances. Pinned channel + explicit opt-in only.
- No self-modifying code (no downloading and executing code from the app process).

## Open questions

- Version source of truth: GitHub release API vs a manifest in the repo (rate limits, offline installs).
- Where schema hashes live (per model `getCollectionSchema()` hashed at boot vs stored in Mongo).
- Minimum upgrade-from version and how skipped releases' data migrations are replayed.
- Rollback depth: document manual steps only, or add `jikan:update --rollback` for data migrations that are reversible.

## Suggested phases

1. `jikan:version`, `jikan:update --check`, status field (read-only).
2. Data-migration registry with lazy re-fetch, `--plan`.
3. `jikan:update` orchestration, lock, backup hook, health check.
4. Docker entrypoint wiring and release channels in CI.
