# Data quality checks

`php artisan jikan:data-quality` reports:

- duplicate `mal_id` values in the core collections;
- broken genre/theme/demographic references;
- broken producer/studio/licensor references;
- broken manga serialization references.

Options:

```bash
php artisan jikan:data-quality
php artisan jikan:data-quality --json
php artisan jikan:data-quality --limit=10
```

Exit code is `0` when clean and `1` when issues are found. The command is
read-only and samples issue IDs for the report.

## Expected baseline

On a partially indexed catalogue, reference collections may not be populated
yet. For example, genre/producer collections are filled by their own indexers;
until then, `broken_ref` counts are expected. Re-run after the relevant
metadata indexers finish before treating the output as a regression.
