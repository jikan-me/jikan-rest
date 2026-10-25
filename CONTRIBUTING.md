# Contributing to Jikan REST API

Thanks for helping out! Security issues go through [SECURITY.md](SECURITY.md), not public issues.

## Requirements

- PHP **>= 8.4.4** with `ext-mongodb` 2.x and `ext-pcntl`, Composer 2
- MongoDB server
- Optional: Typesense (only for `SCOUT_DRIVER=typesense` search), Redis

## Setup

Follow the self-hosting section of the [README](README.MD) and [docs/SETUP.md](docs/SETUP.md)
(native, `./install.sh`) or [docs/DOCKER.md](docs/DOCKER.md) (`docker compose up -d --build`).

## Checks

Run these before opening a pull request (CI runs PHPUnit, PHPStan and Pint):

```bash
composer phpunit                         # = vendor/bin/phpunit --no-coverage (needs MongoDB)
composer phpstan                         # PHPStan, must report 0 errors
vendor/bin/pint --test                   # code style (whole repo, rules in pint.json)
```

Formatting is defined by `pint.json`; run `vendor/bin/pint` before committing.

Point `DB_DATABASE` at a throwaway database when running the tests
(e.g. `DB_DATABASE=jikan_tests composer phpunit`) — the suite drops it.

### Pre-commit hook (optional)

The repo ships [backend-quality-suite](https://github.com/irfan-dahir/backend-quality-suite)
audit config in `.backend-suite/`. To gate your commits on its pre-commit audits, clone that
repo and run its installer from this repo's root:

```bash
python3 <backend-quality-suite>/plugins/backend-suite/bin/install_precommit_hook.py --dir .
```

It writes `.git/hooks/pre-commit`. Don't bypass it with `--no-verify`; fix the finding instead.

## Commits

Use [Conventional Commits](https://www.conventionalcommits.org/), as in the existing history:
`feat:`, `fix(cache): …`, `docs: …`, `ci: …`, `test: …`, `chore(deps): …`, `build(docker): …`.
Keep one logical change per commit.

## Pull requests

- Add or update tests for the behaviour you change; keep PHPUnit, PHPStan and Pint green.
- If you touch routes, controllers, requests/resources or OpenAPI annotations, regenerate and
  commit the generated artifacts — CI's `artifact-freshness` job fails if they are stale:

  ```bash
  php artisan l5-swagger:generate
  composer endpoints:write
  ```

- Describe what changed and why, and note any new `.env` keys (add them to `.env.dist`).
