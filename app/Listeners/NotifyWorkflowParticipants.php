<?php

namespace App\Listeners;

use App\Events\DocumentWorkflowStateChanged;
use App\Jobs\ProcessNotificationJob;
use Illuminate\Support\Facades\Log;

class NotifyWorkflowParticipants
{

    /**
     * Handle the event
     */
    public function handle(DocumentWorkflowStateChanged $event): void
    {
        Log::info('NotifyWorkflowParticipants: Processing workflow state change', [
            'document_id' => $event->document->id,
            'action_status' => $event->actionStatus
        ]);

        try {
            // Create notification context
            $context = $event->createNotificationContext();

            // Dispatch the processing job
            $job = new ProcessNotificationJob($context);
            dispatch($job)->onQueue('notifications-high'); // High priority for workflow notifications

            Log::info('NotifyWorkflowParticipants: Notification processing job dispatched', [
                'document_id' => $event->document->id,
                'action_status' => $event->actionStatus,
                'job_class' => get_class($job)
            ]);

        } catch (\Throwable $e) {
            Log::error('NotifyWorkflowParticipants: Failed to process workflow state change', [
                'document_id' => $event->document->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e; // Re-throw to trigger retry mechanism
        }
    }

}
