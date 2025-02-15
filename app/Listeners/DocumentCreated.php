<?php

namespace App\Listeners;

use App\Events\DocumentControl;
use App\Jobs\HandleDocumentWorkflow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DocumentCreated
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
    public function handle(DocumentControl $event): void
    {
        HandleDocumentWorkflow::dispatch($event->user, $event->document, $event->action, $event->targetStageOrder, $event->status, $event->signature);
    }
}
