<?php
use PackageVersions\Versions;

require_once __DIR__.'/../vendor/autoload.php';

/*
    Defines
*/
defined('JIKAN_PARSER_VERSION') or define('JIKAN_PARSER_VERSION', Versions::getVersion('jikan-me/jikan'));
