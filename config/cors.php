<?php
return [
    'paths' => ['*'],
    'allowed_methods' => ['GET', 'OPTIONS'],
    'allowed_origins' => ['*'],
    'allowed_headers' => ['Accept,Accept-Encoding,DNT,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Content-Range,Range'],
    'max_age' => 86400,
    'supports_credentials' => false,
];
