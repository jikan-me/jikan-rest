# `?fields=` support

`?fields=` is opt-in: when absent, the v4 response is byte-identical. When
present, the middleware projects **top-level keys** from the response's `data`
payload. This applies to every v4 JSON endpoint with a `data` payload, including
scraped, cached and database-backed responses.

## Behaviour

- Applies to successful JSON responses whose body contains `data`.
- Works for both a single `data` object and a list of `data` objects.
- Unknown fields are a `400` when the response has a shape to validate against.
- Empty result sets return unchanged (there is no shape to validate).
- Nested selection is not supported; only top-level keys are selectable.
- `?ids=` remains limited to anime/manga/character search.

## Tests

- `SelectFieldsTest` — projection, collections, unknown fields, arbitrary shapes
- `AnimeControllerTest` — anime detail and full detail
- `MangaControllerTest`, `CharacterControllerTest` — detail
- `AnimeSearchEndpointTest`, `MangaSearchEndpointTest`,
  `SearchControllerTest` — search, batch, and cross-family fields
- `ConditionalCacheTest` — weak ETag and 304
- `ResponseFieldsCoverageTest` — every named v4 route carries the middleware
