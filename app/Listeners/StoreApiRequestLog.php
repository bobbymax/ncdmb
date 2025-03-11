<?php

namespace App\Listeners;

use App\Events\ApiRequestLogged;
use App\Models\ApiRequestLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StoreApiRequestLog implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * Handle the event.
     */
    public function handle(ApiRequestLogged $event): void
    {
        ApiRequestLog::create($event->logData);
    }
}
