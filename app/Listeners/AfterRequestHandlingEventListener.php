<?php

namespace App\Listeners;

use Illuminate\Support\Facades\DB;
use pushrbx\LumenRoadRunner\Events\AfterRequestHandlingEvent;

class AfterRequestHandlingEventListener
{
    public function handle(AfterRequestHandlingEvent $_): void
    {
        DB::disconnect();
    }
}
