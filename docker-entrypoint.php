#!/usr/bin/env php
<?php

use Dotenv\Dotenv;
use Illuminate\Support\Env;

require_once __DIR__.'/vendor/autoload.php';

$safe_defaults = [
    // mongodb regex search by default
    "SCOUT_DRIVER" => "null",
    "SCOUT_QUEUE" => false,
    "THROTTLE" => false,
    "QUEUE_CONNECTION" => "database",
    "DB_CACHING" => true,
    "DB_HOST" => "localhost",
    "DB_PORT" => 27017,
    "DB_DATABASE" => "jikan",
    "DB_USERNAME" => "",
    "DB_PASSWORD" => ""
];

if (!file_exists(".env")) {
    copy(".env.dist", ".env");
    $writer = new \MirazMac\DotEnv\Writer(__DIR__ . '/' . '.env');

    foreach ($safe_defaults as $env_var_name => $env_var_default) {
        $writer->set("SCOUT_DRIVER", env($env_var_name, $env_var_default));
    }
    $writer->write();
}

$dotenv = Dotenv::create(
    Env::getRepository(),
    __DIR__
);
$current_env = $dotenv->load();

if ($current_env["SCOUT_DRIVER"] === "typesense" && empty($current_env["TYPESENSE_API_KEY"])) {
    echo "Please set the TYPESENSE_API_KEY environment variable when setting SCOUT_DRIVER to typesense.";
    exit(1);
}

$rrConfig = \Symfony\Component\Yaml\Yaml::parse(file_get_contents(".rr.yaml"));
$rrConfig["http"]["pool"]["supervisor"]["max_worker_memory"] = (int) env("RR_MAX_WORKER_MEMORY", 128);
$rrConfig["http"]["max_request_size"] = (int) env("RR_MAX_REQUEST_SIZE_MB", 256);
file_put_contents(".rr.yaml", \Symfony\Component\Yaml\Yaml::dump($rrConfig, 8));
