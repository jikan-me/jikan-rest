<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Jikan REST API v4',
    version: '4.2.2',
    description: 'Unofficial MyAnimeList.net REST API',
    contact: new OA\Contact(
        name: 'API Support',
        url: 'http://discord.jikan.moe'
    ),
    license: new OA\License(
        name: 'MIT',
        url: 'https://github.com/jikan-me/jikan-rest/blob/master/LICENSE'
    )
)]

#[OA\Server(
    description: 'Jikan REST API',
    url: 'https://api.jikan.moe/v4'
)]

#[OA\ExternalDocumentation(
    description: 'About',
    url: 'https://jikan.moe'
)]
class Swagger {}
