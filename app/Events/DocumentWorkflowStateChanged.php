<?php

namespace App\Events;

use App\DTOs\NotificationContext;
use App\Models\Document;
use App\Traits\TrackerResolver;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentWorkflowStateChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels, TrackerResolver;

    public function __construct(
        public Document $document,
        public string $actionStatus,
        public array $trackers,
        public array $loggedInUser,
        public array $watchers = []
    ) {}

    /**
     * Create a notification context from this event
     */
    public function createNotificationContext(): NotificationContext
    {
        // Find current tracker
        $currentTracker = $this->findCurrentTracker($this->document->pointer, $this->trackers);

        // Find previous tracker (only for 'passed' status)
        $previousTracker = null;
        if ($this->actionStatus === 'passed') {
            $previousTracker = $this->findPreviousTracker($currentTracker, $this->trackers);
        }

        return new NotificationContext(
            documentId: $this->document->id,
            currentTracker: $currentTracker,
            previousTracker: $previousTracker,
            trackers: $this->trackers,
            loggedInUser: $this->loggedInUser,
            actionStatus: $this->actionStatus,
            watchers: $this->watchers,
            meta_data: $this->document->meta_data
        );
    }
}
