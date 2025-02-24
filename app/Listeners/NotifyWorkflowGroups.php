<?php

namespace App\Listeners;

use App\Engine\MailingEngine;
use App\Events\WorkflowStateChange;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyWorkflowGroups implements ShouldQueue
{
    protected MailingEngine $engine;
    /**
     * Create the event listener.
     */
    public function __construct(MailingEngine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Handle the event.
     */
    public function handle(WorkflowStateChange $event): void
    {
        $this->engine->notifyStageTransition(
            $event->document,
            $event->documentAction,
            $event->userId,
            $event->previousTracker,
            $event->nextTracker
        );
    }
}
