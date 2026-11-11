# `?fields=` support matrix

`?fields=` is opt-in and intentionally limited to the highest-volume response
shapes. When the parameter is absent, the v4 response is unchanged.

## Supported

| Route | Resource | Allowlist |
|---|---|---|
| `GET /v4/anime` | anime | `config/response_fields.php` |
| `GET /v4/anime/{id}` | anime | `config/response_fields.php` |
| `GET /v4/manga` | manga | `config/response_fields.php` |
| `GET /v4/manga/{id}` | manga | `config/response_fields.php` |
| `GET /v4/characters` | character | `config/response_fields.php` |
| `GET /v4/characters/{id}` | character | `config/response_fields.php` |

`?ids=` is supported on the three search routes above, bounded to 50 ids.

## Not supported

Every other v4 endpoint ignores `?fields=`; it does not error and does not
project the response. Notable candidates for a later tier:

- `GET /v4/anime/{id}/full`, `/manga/{id}/full`, `/characters/{id}/full`
- `GET /v4/people`, `/people/{id}`, `/producers`, `/producers/{id}`,
  `/clubs`, `/clubs/{id}`
- top, seasons, schedules, recommendations, reviews, users, watch, random

Adding a route requires a resource allowlist in `config/response_fields.php`
and a route mapping; `ResponseFieldsConfigTest` guards the configured entries.

## Tests

- `SelectFieldsTest` — projection, collections, unknown fields
- `AnimeControllerTest`, `MangaControllerTest`, `CharacterControllerTest` — detail
- `AnimeSearchEndpointTest`, `MangaSearchEndpointTest`,
  `SearchControllerTest` — search
- `ConditionalCacheTest` — weak ETag and 304
- `ResponseFieldsConfigTest` — configured routes/resources and explicit scope
