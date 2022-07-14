<?php

namespace App\Listeners;

use pushrbx\LumenRoadRunner\Events\Contracts\WithApplication;
use pushrbx\LumenRoadRunner\Listeners\ListenerInterface;

class PsrWorkerBeforeRequestHandlingListener implements ListenerInterface
{

    public function handle($event): void
    {
        if ($event instanceof WithApplication) {
            $app = $event->application();

            $serviceProviderClass = "";

            if (env("SCOUT_DRIVER") === "typesense") {
                $serviceProviderClass = \Typesense\LaravelTypesense\TypesenseServiceProvider::class;
            }

            if (env("SCOUT_DRIVER") === "Matchish\ScoutElasticSearch\Engines\ElasticSearchEngine") {
                $serviceProviderClass = \Matchish\ScoutElasticSearch\ElasticSearchServiceProvider::class;
            }

            if ($serviceProviderClass !== "") {
                $provider = new $serviceProviderClass($app);

                $provider->register();

                if (\method_exists($provider, $boot_method = 'boot')) {
                    $app->call([$provider, $boot_method]);
                }
            }
        }
    }
}
