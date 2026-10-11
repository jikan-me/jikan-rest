<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jikan REST API v4 — API Reference</title>
</head>
<body>
    <div id="app"></div>

    <script type="module">
        import { createApiReference } from 'https://cdn.jsdelivr.net/npm/@scalar/api-reference@1.73.1/esm.js'

        createApiReference('#app', {
            // The existing l5-swagger OpenAPI JSON (same origin as this page)
            url: {{ Js::from(url('v4/api-docs')) }},
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