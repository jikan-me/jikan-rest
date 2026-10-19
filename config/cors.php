<?php

return [
    // Opt-in, as on the Lumen release (CORS_MIDDLEWARE, default off): when an edge proxy
    // already answers CORS, a second Access-Control-Allow-Origin makes browsers reject
    // the response. An empty list turns Laravel's global HandleCors into a no-op.
    'paths' => env('CORS_MIDDLEWARE', false) ? ['*'] : [],
    'allowed_methods' => ['GET', 'OPTIONS'],
    'allowed_origins' => ['*'],
    'allowed_headers' => ['Accept,Accept-Encoding,DNT,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Content-Range,Range'],
    'max_age' => 86400,
    'supports_credentials' => false,
];
