<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jikan REST API v4 — API Reference</title>
    <link rel="stylesheet" href="{{ asset('vendor/scalar/style.css') }}">
</head>
<body>
    <div id="app"></div>

    {{-- Self-hosted Scalar API Reference (@scalar/api-reference@1.73.1, UMD standalone bundle) — no runtime CDN dependency --}}
    <script src="{{ asset('vendor/scalar/standalone.js') }}"></script>
    <script>
        Scalar.createApiReference('#app', {
            // The existing l5-swagger OpenAPI JSON (same origin as this page)
            url: {{ Js::from(url('v4/docs/openapi')) }},
            theme: 'default',
            // Docs-only viewer: no in-browser requests hitting the rate-limited API
            hideTestRequestButton: true,
            showDeveloperTools: 'never',
            // Never upload our public spec to Scalar's hosted Agent AI
            agent: { disabled: true },
            metaData: {
                title: 'Jikan REST API v4',
                description: 'Unofficial MyAnimeList.net REST API — generated from the OpenAPI spec',
            },
        })
    </script>
</body>
</html>