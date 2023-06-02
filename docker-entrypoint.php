#!/usr/bin/env php
<?php

use Dotenv\Dotenv;

require_once __DIR__.'/vendor/autoload.php';

$safe_defaults = [
    // mongodb regex search by default
    "SCOUT_DRIVER" => "none",
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


$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$current_env = $_ENV;

if ($current_env["SCOUT_DRIVER"] === "typesense" && empty($current_env["TYPESENSE_API_KEY"])) {
    echo "Please set the TYPESENSE_API_KEY environment variable when setting SCOUT_DRIVER to typesense.";
    exit(1);
}

$rrConfig = \Symfony\Component\Yaml\Yaml::parse(file_get_contents(".rr.yaml"));
$rrConfig["http"]["pool"]["supervisor"]["max_worker_memory"] = (int) env("RR_MAX_WORKER_MEMORY", 128);
$rrConfig["http"]["max_request_size"] = (int) env("RR_MAX_REQUEST_SIZE_MB", 256);
$rrConfig["service"]["laravel_queue_worker_1"]["process_num"] = (int) env("JIKAN_QUEUE_WORKER_PROCESS_NUM", 1);
$periodical_full_indexer_key = "JIKAN_ENABLE_PERIODICAL_FULL_INDEXER";
if (array_key_exists($periodical_full_indexer_key, $current_env) && in_array($current_env[$periodical_full_indexer_key], [1, '1', 'true', 'True', 'TRUE'])) {
    $supercronic_schedule = file_get_contents("/etc/supercronic/laravel");
    $supercronic_schedule .= PHP_EOL;
    $supercronic_schedule .= "0 1 * * 1 php /app/artisan indexer:anime --fail && php /app/artisan indexer:anime --resume && php /app/artisan indexer:manga --fail && php /app/artisan indexer:manga --resume";
    $supercronic_schedule .= PHP_EOL;
    file_put_contents("/etc/supercronic/laravel", $supercronic_schedule);
    $current_time = time();
    echo json_encode(["level" => "info", "ts" => "$current_time.0", "logger" => "container_entrypoint", "msg" => "Full anime/manga indexer is enabled. They will run every Monday at 1am."]) . PHP_EOL;
}
file_put_contents(".rr.yaml", \Symfony\Component\Yaml\Yaml::dump($rrConfig, 8));
