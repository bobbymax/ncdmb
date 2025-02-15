<?php

namespace App\Listeners;

use App\Events\FirstDraft;
use App\Jobs\HandleDocumentFirstDraft;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateDocumentFirstDraft
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(FirstDraft $event): void
    {
        HandleDocumentFirstDraft::dispatch($event->document, $event->user);
    }
}
