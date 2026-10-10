<?php

/*
 * Compatibility shim for the abandoned "fabpot/goutte" package.
 *
 * jikan-me/jikan v4 depends on fabpot/goutte ^4.0, which caps symfony/mime at
 * ^6.0 and therefore cannot coexist with Laravel 12's Symfony 7 components.
 * Goutte's Client is itself only a thin wrapper around Symfony's HttpBrowser,
 * so we provide it here and tell Composer to replace fabpot/goutte.
 *
 * Upstream: https://github.com/FriendsOfPHP/Goutte/blob/v4.0.3/Goutte/Client.php
 * (c) Fabien Potencier <fabien@symfony.com>, MIT licensed.
 */

namespace Goutte;

use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\BrowserKit\History;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @deprecated Use Symfony\Component\BrowserKit\HttpBrowser directly instead.
 */
class Client extends HttpBrowser
{
    public function __construct(HttpClientInterface $client = null, History $history = null, CookieJar $cookieJar = null)
    {
        parent::__construct($client, $history, $cookieJar);
    }
}
