<?php

return [
    'api' => [
        /*
        |--------------------------------------------------------------------------
        | Edit to set the api's title
        |--------------------------------------------------------------------------
         */
        'title' => 'Swagger Lume API',
    ],

    'routes' => [
        /*
        |--------------------------------------------------------------------------
        | Route for accessing api documentation interface
        |--------------------------------------------------------------------------
         */
        'api' => '/api/documentation',

        /*
        |--------------------------------------------------------------------------
        | Route for accessing parsed swagger annotations.
        |--------------------------------------------------------------------------
         */
        'docs' => '/docs',

        /*
        |--------------------------------------------------------------------------
        | Route for Oauth2 authentication callback.
        |--------------------------------------------------------------------------
        */
        'oauth2_callback' => '/api/oauth2-callback',

        /*
        |--------------------------------------------------------------------------
        | Route for serving assets
        |--------------------------------------------------------------------------
        */
        'assets' => '/swagger-ui-assets',

        /*
        |--------------------------------------------------------------------------
        | Middleware allows to prevent unexpected access to API documentation
        |--------------------------------------------------------------------------
         */
        'middleware' => [
            'api' => [],
            'asset' => [],
            'docs' => [],
            'oauth2_callback' => [],
        ],
    ],

    'paths' => [
        /*
        |--------------------------------------------------------------------------
        | Absolute path to location where parsed swagger annotations will be stored
        |--------------------------------------------------------------------------
         */
        'docs' => storage_path('api-docs'),

        /*
        |--------------------------------------------------------------------------
        | File name of the generated json documentation file
        |--------------------------------------------------------------------------
        */
        'docs_json' => 'api-docs.json',

        /*
        |--------------------------------------------------------------------------
        | Absolute path to directory containing the swagger annotations are stored.
        |--------------------------------------------------------------------------
         */
        'annotations' => base_path('app'),

        /*
        |--------------------------------------------------------------------------
        | Absolute path to directories that you would like to exclude from swagger generation
        |--------------------------------------------------------------------------
         */
        'excludes' => [],

        /*
        |--------------------------------------------------------------------------
        | Edit to set the swagger scan base path
        |--------------------------------------------------------------------------
        */
        'base' => env('L5_SWAGGER_BASE_PATH', null),

        /*
        |--------------------------------------------------------------------------
        | Absolute path to directory where to export views
        |--------------------------------------------------------------------------
         */
        'views' => base_path('resources/views/vendor/swagger-lume'),
    ],

    /*
    |--------------------------------------------------------------------------
    | API security definitions. Will be generated into documentation file.
    |--------------------------------------------------------------------------
    */
    'security' => [
        /*
        |--------------------------------------------------------------------------
        | Examples of Security definitions
        |--------------------------------------------------------------------------
        */
        /*
        'api_key_security_example' => [ // Unique name of security
            'type' => 'apiKey', // The type of the security scheme. Valid values are "basic", "apiKey" or "oauth2".
            'description' => 'A short description for security scheme',
            'name' => 'api_key', // The name of the header or query parameter to be used.
            'in' => 'header', // The location of the API key. Valid values are "query" or "header".
        ],
        'oauth2_security_example' => [ // Unique name of security
            'type' => 'oauth2', // The type of the security scheme. Valid values are "basic", "apiKey" or "oauth2".
            'description' => 'A short description for oauth2 security scheme.',
            'flow' => 'implicit', // The flow used by the OAuth2 security scheme. Valid values are "implicit", "password", "application" or "accessCode".
            'authorizationUrl' => 'http://example.com/auth', // The authorization URL to be used for (implicit/accessCode)
            //'tokenUrl' => 'http://example.com/auth' // The authorization URL to be used for (password/application/accessCode)
            'scopes' => [
                'read:projects' => 'read your projects',
                'write:projects' => 'modify projects in your account',
            ]
        ],*/

        /* Open API 3.0 support
        'passport' => [ // Unique name of security
            'type' => 'oauth2', // The type of the security scheme. Valid values are "basic", "apiKey" or "oauth2".
            'description' => 'Laravel passport oauth2 security.',
            'in' => 'header',
            'scheme' => 'https',
            'flows' => [
                "password" => [
                    "authorizationUrl" => config('app.url') . '/oauth/authorize',
                    "tokenUrl" => config('app.url') . '/oauth/token',
                    "refreshUrl" => config('app.url') . '/token/refresh',
                    "scopes" => []
                ],
            ],
        ],
        */
    ],

    /*
    |--------------------------------------------------------------------------
    | Turn this off to remove swagger generation on production
    |--------------------------------------------------------------------------
     */
    'generate_always' => env('SWAGGER_GENERATE_ALWAYS', false),

    /*
    |--------------------------------------------------------------------------
    | Edit to set the swagger version number
    |--------------------------------------------------------------------------
     */
    'swagger_version' => env('SWAGGER_VERSION', '3.0'),

    /*
    |--------------------------------------------------------------------------
    | Edit to trust the proxy's ip address - needed for AWS Load Balancer
    |--------------------------------------------------------------------------
     */
    'proxy' => false,

    /*
    |--------------------------------------------------------------------------
    | Configs plugin allows to fetch external configs instead of passing them to SwaggerUIBundle.
    | See more at: https://github.com/swagger-api/swagger-ui#configs-plugin
    |--------------------------------------------------------------------------
    */

    'additional_config_url' => null,

    /*
    |--------------------------------------------------------------------------
    | Apply a sort to the operation list of each API. It can be 'alpha' (sort by paths alphanumerically),
    | 'method' (sort by HTTP method).
    | Default is the order returned by the server unchanged.
    |--------------------------------------------------------------------------
    */

    'operations_sort' => env('L5_SWAGGER_OPERATIONS_SORT', null),

    /*
    |--------------------------------------------------------------------------
    | Uncomment to pass the validatorUrl parameter to SwaggerUi init on the JS
    | side.  A null value here disables validation.
    |--------------------------------------------------------------------------
    */

    'validator_url' => null,

    /*
    |--------------------------------------------------------------------------
    | Uncomment to add constants which can be used in anotations
    |--------------------------------------------------------------------------
     */
    'constants' => [
        // 'SWAGGER_LUME_CONST_HOST' => env('SWAGGER_LUME_CONST_HOST', 'http://my-default-host.com'),
        'API_DESCRIPTION' => <<<EOF
        [Jikan](https://jikan.moe) is an **Unofficial** MyAnimeList API.
        It scrapes the website to satisfy the need for a complete API - which MyAnimeList lacks.
        
        # Information

        ⚡ Jikan is powered by it's awesome backers - 🙏 [Become a backer](https://www.patreon.com/jikan)

        ## Rate Limiting

        | Duration | Requests |
        |----|----|
        | Daily | **Unlimited** |
        | Per Minute | 60 requests |
        | Per Second | 3 requests |


        ## JSON Notes
        - Any property (except arrays or objects) whose value does not exist or is undetermined, will be `null`.
        - Any array or object property whose value does not exist or is undetermined, will be `null`.
        - Any `score` property whose value does not exist or is undetermined, will be `0`.
        - All dates and timestamps are returned in [ISO8601](https://en.wikipedia.org/wiki/ISO_8601) format and in UTC timezone

        ## Caching
        By **CACHING**, we refer to the data parsed from MyAnimeList which is stored temporarily on our servers to provide better API performance.

        All requests, by default are cached for **24 hours** except the following endpoints which have their own unique cache **Time To Live**.

        | Request | TTL |
        | ---- | ---- |
        | All (Default) | 24 hours |
        | User Anime/Manga List | 5 minutes |


        The following response headers will detail cache information.

        | Header | Remarks |
        | ---- | ---- |
        | `Expires` | Expiry unix timestamp |


        ## Allowed HTTP(s) requests

        **Jikan REST API does not provide authenticated requests for MyAnimeList.** This means you can not use it to update your anime/manga list.
        Only GET requests are supported which return READ-ONLY data.

        ## HTTP Responses

        | HTTP Status | Remarks |
        | ---- | ---- |
        | `200 - OK` | The request was successful |
        | `304 - Not Modified` | You have the latest data (Cache Validation response) |
        | `400 - Bad Request` | You've made an invalid request. Recheck documentation |
        | `404 - Not Found` | The resource was not found or MyAnimeList responded with a `404` |
        | `405 - Method Not Allowed` | Requested Method is not supported for resource. Only `GET` requests are allowed |
        | `429 - Too Many Request` | You are being rate limited by Jikan or MyAnimeList is rate-limiting our servers (specified in the error response) |
        | `500 - Internal Server Error` | Something is not working on our end. If you see an error response with a `report_url` URL, please click on it to open an auto-generated GitHub issue |
        | `503 - Service Unavailable` | The service has broke. |


        ## JSON Error Response

        ```json
         {
             "status": 404,
             "type": "BadResponseException",
             "message": "Resource does not exist",
             "error": "Something Happened",
             "report_url": "https://github.com..."
          }
        ```

        | Property | Remarks |
        | ---- | ---- |
        | `status` | Returned HTTP Status Code |
        | `type` | Thrown Exception |
        | `message` | Human-readable error message |
        | `error` | Error response and trace from the API |
        | `report_url` | Clicking this would redirect you to a generated GitHub issue. ℹ It's only returned on a parser error. |


        ## Cache Validation

        - All requests return a `ETag` header which is an MD5 hash of the response
        - You can use this hash to verify if there's new or updated content by suppliying it as the value for the `If-None-Match` in your next request header
        - You will get a HTTP `304 - Not Modified` response if the content has not changed
        - If the content has changed, you'll get a HTTP `200 - OK` response with the updated JSON response

        ![Cache Validation](https://i.imgur.com/925ozVn.png 'Cache Validation')

        ## Disclaimer

        - Jikan is not affiliated with MyAnimeList.net.
        - Jikan is a free, open-source API. Please use it responsibly.

        ----

        By using the API, you are agreeing to Jikan's [terms of use](https://jikan.moe/terms) policy.

        [v3 Documentation](https://jikan.docs.apiary.io/) - [Wrappers/SDKs](https://github.com/jikan-me/jikan#wrappers) - [Report an issue](https://github.com/jikan-me/jikan-rest/issues/new) - [Host your own server](https://github.com/jikan-me/jikan-rest)
        EOF,
    ],
];
