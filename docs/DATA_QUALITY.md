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

### Baseline mode

```bash
php artisan jikan:data-quality --write-baseline=storage/quality-baseline.json
php artisan jikan:data-quality --baseline=storage/quality-baseline.json
```

With a baseline, known counts are accepted and only **new** findings make the
command exit `1`:

```bash
php artisan jikan:data-quality --baseline=storage/quality-baseline.json
```

Regenerate the baseline after intentionally changing the catalogue.

### CI

The `Data quality · baseline gate` job runs `DataQualityCommandTest` (empty
catalogue, duplicates, broken references, baseline suppression and baseline
growth) plus a smoke run of the command.

CI has no production catalogue, so the catalogue-wide gate stays a post-index
step: after a bulk index or a data migration, run

```bash
php artisan jikan:data-quality --baseline=storage/quality-baseline.json
```

and ship the baseline with the data snapshot. A non-zero exit means the new data
introduced findings that the previous snapshot did not have.

## Expected baseline

On a partially indexed catalogue, reference collections may not be populated
yet. For example, genre/producer collections are filled by their own indexers;
until then, `broken_ref` counts are expected. Re-run after the relevant
metadata indexers finish before treating the output as a regression.
