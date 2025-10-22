<?php

namespace App\Jobs;

use App\Models\Document;
use App\Notifications\WorkflowDistributionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class DispatchNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public string $email;
    public string $name;
    public int $actionId;
    public string $type;
    public Document $document;

    public int $tries = 3;
    public array $backoff = [5, 15, 30];
    public string $queue = 'notifications';

    /**
     * Create a new job instance.
     */
    public function __construct(
        Document $document,
        string $email,
        string $name,
        string $type,
        int $actionId
    ) {
        $this->email = $email;
        $this->name = $name;
        $this->type = $type;
        $this->document = $document;
        $this->actionId = $actionId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!$this->email) {
                Log::warning('DispatchNotificationJob: No email provided', [
                    'document_id' => $this->document->id,
                    'action_id' => $this->actionId,
                    'type' => $this->type
                ]);
                return;
            }

            // Validate email format
            if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                Log::error('DispatchNotificationJob: Invalid email format', [
                    'email' => $this->email,
                    'document_id' => $this->document->id
                ]);
                return;
            }

            // Ensure document exists and is loaded
            if (!$this->document || !$this->document->exists) {
                Log::error('DispatchNotificationJob: Document not found or deleted', [
                    'document_id' => $this->document->id ?? 'unknown',
                    'action_id' => $this->actionId
                ]);
                return;
            }

            // Send notification
            Notification::route('mail', $this->email)
                ->notify(new WorkflowDistributionNotification(
                    $this->document,
                    $this->actionId,
                    $this->type,
                    $this->name
                ));

            Log::info('DispatchNotificationJob: Notification sent successfully', [
                'email' => $this->email,
                'document_id' => $this->document->id,
                'action_id' => $this->actionId,
                'type' => $this->type
            ]);

        } catch (\Throwable $e) {
            Log::error('DispatchNotificationJob: Failed to send notification', [
                'email' => $this->email,
                'document_id' => $this->document->id ?? 'unknown',
                'action_id' => $this->actionId,
                'type' => $this->type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e; // Re-throw to trigger retry mechanism
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('DispatchNotificationJob: Job failed permanently', [
            'email' => $this->email,
            'document_id' => $this->document->id ?? 'unknown',
            'action_id' => $this->actionId,
            'type' => $this->type,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
