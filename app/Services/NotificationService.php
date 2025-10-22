<?php

namespace App\Services;

use App\DTOs\NotificationContext;
use App\Events\DocumentWorkflowStateChanged;
use App\Jobs\ProcessNotificationJob;
use App\Models\Document;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function __construct(
        protected RecipientResolverService $recipientResolver,
        protected NotificationTemplateService $templateService
    ) {}

    /**
     * @throws \Throwable
     */
    public function notify(NotificationContext $context): void
    {
        Log::info('NotificationService: Starting notification dispatch', [
            'document_id' => $context->documentId,
            'action_status' => $context->actionStatus,
            'job_class' => ProcessNotificationJob::class
        ]);

        try {
            // Validate context before dispatching
            if (!$context->isValid()) {
                $missingFields = $context->getMissingFields();
                Log::error('NotificationService: Invalid context provided', [
                    'document_id' => $context->documentId,
                    'missing_fields' => $missingFields
                ]);
                throw new \InvalidArgumentException('Invalid notification context: ' . implode(', ', $missingFields));
            }

            // Get queue configuration - use default queue for now
            $queueName = 'default';

            // Validate queue name (basic check)
            if (empty($queueName)) {
                Log::warning('NotificationService: Empty queue name, using default', [
                    'document_id' => $context->documentId
                ]);
                $queueName = 'default';
            }

            Log::info('NotificationService: Using queue configuration', [
                'queue_name' => $queueName,
                'queue_connection' => config('queue.default', 'database')
            ]);

            Log::info('NotificationService: Dispatching ProcessNotificationJob', [
                'document_id' => $context->documentId,
                'action_status' => $context->actionStatus,
                'queue_name' => $queueName,
                'job_class' => ProcessNotificationJob::class
            ]);

            // Dispatch the job — use afterCommit and afterResponse to avoid blocking the request.
            ProcessNotificationJob::dispatch($context)
                ->onQueue($queueName)
                ->afterCommit()
                ->afterResponse();
                // Temporarily removed afterCommit() and afterResponse() for debugging

            Log::info('NotificationService: ProcessNotificationJob dispatched successfully', [
                'document_id' => $context->documentId,
                'action_status' => $context->actionStatus,
                'queue_name' => $queueName
            ]);

        } catch (\Throwable $e) {
            Log::error('NotificationService: Failed to dispatch notification job', [
                'document_id' => $context->documentId,
                'action_status' => $context->actionStatus,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e; // Re-throw to trigger error handling in controller
        }
    }

    /**
     * Process workflow notifications for a document action
     */
    public function processWorkflowNotifications(
        Document $document,
        string $actionStatus,
        array $trackers,
        array $loggedInUser,
        array $watchers = []
    ): void {
        try {
            // Validate required data
            if (empty($trackers)) {
                Log::warning("No trackers provided for document notifications", [
                    'document_id' => $document->id
                ]);
                return;
            }

            $currentPointer = $document->pointer;
            if (empty($currentPointer)) {
                Log::warning("No current pointer found for document", [
                    'document_id' => $document->id
                ]);
                return;
            }

            // Check if notifications are enabled
            if (!config('notifications.global.enable_notifications', true)) {
                Log::info('Notifications are disabled globally', [
                    'document_id' => $document->id
                ]);
                return;
            }

            // Dispatch the event
            event(new DocumentWorkflowStateChanged(
                document: $document,
                actionStatus: $actionStatus,
                trackers: $trackers,
                loggedInUser: $loggedInUser,
                watchers: $watchers
            ));

            Log::info('Document workflow state change event dispatched', [
                'document_id' => $document->id,
                'action_status' => $actionStatus,
                'tracker_count' => count($trackers),
                'watcher_count' => count($watchers)
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to process workflow notifications', [
                'document_id' => $document->id,
                'action_status' => $actionStatus,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Don't re-throw to avoid breaking the main process
        }
    }

    /**
     * Process a notification context directly
     */
    public function processNotificationContext(NotificationContext $context): void
    {
        try {
            // Dispatch the processing job
            dispatch(new ProcessNotificationJob($context))
                ->onQueue($this->templateService->getQueueForRecipientType('current_tracker'))
                ->afterCommit()
                ->afterResponse();

            Log::info('Notification processing job dispatched', [
                'document_id' => $context->documentId,
                'action_status' => $context->actionStatus
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to dispatch notification processing job', [
                'document_id' => $context->documentId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStats(): array
    {
        // This could be expanded to include actual statistics
        return [
            'enabled' => config('notifications.global.enable_notifications', true),
            'channels' => [
                'mail' => $this->templateService->isChannelEnabled('mail'),
                'sms' => $this->templateService->isChannelEnabled('sms'),
                'database' => $this->templateService->isChannelEnabled('database'),
            ],
            'queues' => config('notifications.workflow.queues', []),
        ];
    }
}
