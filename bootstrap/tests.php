<?php

use App\JikanApiModel;
use PackageVersions\Versions;
use HaydenPierce\ClassFinder\ClassFinder;

require_once __DIR__.'/../vendor/autoload.php';

/*
    Defines
*/
defined('JIKAN_PARSER_VERSION') or define('JIKAN_PARSER_VERSION', Versions::getVersion('jikan-me/jikan'));


$classNamesCachePath = __DIR__ . "/../storage/app";
// this line only works if dev dependencies are installed
$classes = ClassFinder::getClassesInNamespace("App");
$jikanModels = array_values(
    array_filter($classes, fn($class) => is_subclass_of($class, JikanApiModel::class))
);
file_put_contents($classNamesCachePath . "/jikan_model_classes.json", json_encode($jikanModels));

