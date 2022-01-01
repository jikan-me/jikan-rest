<?php

namespace App\Http;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HttpResponse
{

    public static function notFound(Request $request) : Response
    {
        return response(
            \json_encode([
                'status' => 404,
                'type' => 'BadResponseException',
                'message' => 'Resource not found',
                'error' => '404 on ' . $request->getUri()
            ]),
            404
        );
    }

    public static function badRequest(Request $request) : Response
    {
        return response(
            \json_encode([
                'status' => 400,
                'type' => 'BadRequestException',
                'message' => 'Invalid or incomplete request. Make sure your request is correct. https://docs.api.jikan.moe/',
                'error' => null
            ]),
            400
        );
    }
}