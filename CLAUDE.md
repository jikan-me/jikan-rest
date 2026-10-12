# CLAUDE.md

@AGENTS.md

Primary instructions live in **`AGENTS.md`** (imported above) — read it before
acting in this repo. Three rules that matter most:

1. **Never change the public v4 JSON contract** for default requests — only additive,
   opt-in parameters (`fields=`, `?ids=`) are allowed.
2. **Finish green:** run the full suite (`tools/dev/php vendor/bin/phpunit --no-coverage`),
   `php artisan l5-swagger:generate` (exit 0), and the `.backend-suite` audits before
   considering a change done.
3. **Enums are docblock-first:** `app/Enums/*` carry spatie value declarations as
   `@method static self value()` docblock lines — keep them when editing the adjacent
   `#[OA\Schema]` attribute.