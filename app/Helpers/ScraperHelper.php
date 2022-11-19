<?php

namespace App\Helpers;

use App\Providers\SerializerFactory;

class ScraperHelper
{

    /**
     * @param object|array $response
     * @return array
     */
    public static function getSerializedJSON(object|array $response): array
    {
        return \json_decode(
            SerializerFactory::createV4()
                ->serialize($response, 'json'),
            true
        );
    }
}
