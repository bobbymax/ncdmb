<?php

namespace App\Listeners;

use App\Events\DocumentControlCentre;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateDocumentDraft
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
    public function handle(DocumentControlCentre $event): void
    {
        //
    }
}
