<?php

namespace App\Providers;
use Spatie\Enum\Laravel\EnumServiceProvider;

class JikanEnumServiceProvider extends EnumServiceProvider
{
    protected function registerRouteBindingMacro(): void
    {
        // noop
    }
}
