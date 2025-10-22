<?php

namespace App\Jobs;

use App\DTOs\NotificationContext;
use App\Jobs\SendNotificationJob;
use App\Services\RecipientResolverService;
use App\Services\NotificationTemplateService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class ProcessNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, Batchable;

    public int $tries = 3;
    public array $backoff = [5, 15, 30];

    public function __construct(public NotificationContext $context) {}

    /**
     * @throws \Throwable
     */
    public function handle(
        RecipientResolverService $recipientResolver,
        NotificationTemplateService $templateService
    ): void {
        Log::info('ProcessNotificationJob: Starting execution', [
            'document_id' => $this->context->documentId,
            'action_status' => $this->context->actionStatus,
            'current_tracker' => $this->context->currentTracker['identifier'] ?? 'unknown',
            'previous_tracker' => $this->context->previousTracker['identifier'] ?? 'none',
            'tracker_count' => count($this->context->trackers),
            'watcher_count' => count($this->context->watchers),
            'document_ref' => $this->context->documentRef,
            'document_title' => $this->context->documentTitle,
        ]);

        try {
            // Validate context again in job (defensive programming)
            if (!$this->context->isValid()) {
                $missingFields = $this->context->getMissingFields();
                Log::error('ProcessNotificationJob: Invalid context in job execution', [
                    'document_id' => $this->context->documentId,
                    'missing_fields' => $missingFields
                ]);
                throw new \InvalidArgumentException('Invalid notification context in job: ' . implode(', ', $missingFields));
            }

            $jobs = collect();

            // 1. Current Tracker Recipients - HIGHEST PRIORITY (for all statuses)
            $currentRecipients = $recipientResolver->resolveTrackerRecipients($this->context->currentTracker, $this->context->loggedInUser);

            Log::info('ProcessNotificationJob: Current tracker recipients resolved', [
                'current_tracker' => $this->context->currentTracker,
                'recipient_count' => $currentRecipients->count(),
                'recipients' => $currentRecipients->toArray()
            ]);

            if ($currentRecipients->isNotEmpty()) {
                $channels = $templateService->getChannelsForRecipientType('current_tracker');
//                $priority = $templateService->getPriorityForRecipientType('current_tracker');
                $queueName = $templateService->getQueueForRecipientType('current_tracker');

                $job = new SendNotificationJob(
                    recipients: $currentRecipients->toArray(),
                    notificationType: $this->context->actionStatus === 'passed' ? 'pending_action' : 'status_update',
                    context: $this->context,
                    channels: $channels
                );
                $job->onQueue($queueName ?: 'default')->delay(0);
                $jobs->push($job);
            }

            // 2. Previous Tracker Recipients - MEDIUM PRIORITY (only for "passed" status)
            if ($this->context->actionStatus === 'passed' && $this->context->previousTracker) {
                $previousRecipients = $recipientResolver->resolveTrackerRecipients($this->context->previousTracker, $this->context->loggedInUser);

                if ($previousRecipients->isNotEmpty()) {
                    $channels = $templateService->getChannelsForRecipientType('previous_tracker');
                    $queue = $templateService->getQueueForRecipientType('previous_tracker');
//                    $priority = $templateService->getPriorityForRecipientType('previous_tracker');

                    $job = new SendNotificationJob(
                        recipients: $previousRecipients->toArray(),
                        notificationType: 'acknowledgment',
                        context: $this->context,
                        channels: $channels,
                    );
                    $job->onQueue($queue ?: 'default');
                    $jobs->push($job);
                }
            }

            // 3. Document Watchers - LOWEST PRIORITY (for all statuses)
            if (!empty($this->context->watchers)) {
                $watcherRecipients = $recipientResolver->resolveWatcherRecipients($this->context->watchers);

                if ($watcherRecipients->isNotEmpty()) {
                    $channels = $templateService->getChannelsForRecipientType('watchers');
                    $queue = $templateService->getQueueForRecipientType('watchers');

                    $job = new SendNotificationJob(
                        recipients: $watcherRecipients->toArray(),
                        notificationType: 'document_action',
                        context: $this->context,
                        channels: $channels
                    );
                    $job->onQueue($queue ?: 'default');
                    $jobs->push($job);

                    Log::info('ProcessNotificationJob: Added watchers job', [
//                        'priority' => $priority,
                        'recipient_count' => $watcherRecipients->count()
                    ]);
                }
            }

            // If no jobs, nothing to do
            if ($jobs->isEmpty()) {
                Log::info('ProcessNotificationJob: No notification jobs created', ['document_id' => $this->context->documentId]);
                return;
            }

            // Dispatch jobs in a Bus::batch to allow grouped monitoring, allow failures
            Bus::batch($jobs->all())
                ->name("document-notifications:{$this->context->documentId}")
                ->allowFailures()
                ->dispatch();

            Log::info('ProcessNotificationJob: Notification batch dispatched', [
                'document_id' => $this->context->documentId,
                'job_count' => $jobs->count()
            ]);

            // Dispatch all notification jobs in batches by priority
//            $this->dispatchJobsByPriority($jobs);

        } catch (\Throwable $e) {
            Log::error('ProcessNotificationJob: Failed to process notifications', [
                'document_id' => $this->context->documentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e; // Re-throw to trigger retry mechanism
        }
    }

    /**
     * Dispatch jobs grouped by priority
     */
    protected function dispatchJobsByPriority($jobs): void
    {
        if ($jobs->isEmpty()) {
            Log::info('ProcessNotificationJob: No jobs to dispatch');
            return;
        }

        // Group jobs by priority
        $highPriorityJobs = $jobs->filter(fn($job) => $job->priority === 'high');
        $mediumPriorityJobs = $jobs->filter(fn($job) => $job->priority === 'medium');
        $lowPriorityJobs = $jobs->filter(fn($job) => $job->priority === 'low');

        Log::info('ProcessNotificationJob: Jobs grouped by priority', [
            'high_count' => $highPriorityJobs->count(),
            'medium_count' => $mediumPriorityJobs->count(),
            'low_count' => $lowPriorityJobs->count()
        ]);

        // Dispatch high priority jobs first
        if ($highPriorityJobs->isNotEmpty()) {
            $this->dispatchJobBatch($highPriorityJobs, 'high');
        }

        // Dispatch medium priority jobs
        if ($mediumPriorityJobs->isNotEmpty()) {
            $this->dispatchJobBatch($mediumPriorityJobs, 'medium');
        }

        // Dispatch low priority jobs
        if ($lowPriorityJobs->isNotEmpty()) {
            $this->dispatchJobBatch($lowPriorityJobs, 'low');
        }
    }

    /**
     * Dispatch a batch of jobs
     */
    protected function dispatchJobBatch($jobs, string $priority): void
    {
        $documentId = $this->context->documentId;
        $queue = $jobs->first()->queue ?? 'default';

        Log::info("ProcessNotificationJob: Dispatching {$priority} priority jobs", [
            'count' => $jobs->count(),
            'queue' => $queue
        ]);

        Bus::batch($jobs->toArray())
            ->name("document-notifications-{$priority}:{$documentId}")
            ->allowFailures()
            ->onQueue($queue)
            ->dispatch();

        Log::info("ProcessNotificationJob: {$priority} priority jobs dispatched");
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessNotificationJob: Job failed permanently', [
            'document_id' => $this->context->documentId,
            'action_status' => $this->context->actionStatus,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
