<?php

namespace App\Jobs;

use App\DTOs\NotificationContext;
use App\Mail\DocumentActionMail;
use App\Models\User;
use App\Notifications\DocumentActionNotification;
use App\Notifications\DocumentWorkflowNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public int $tries = 3;
    public array $backoff = [5, 15, 30];

    public function __construct(
        public array $recipients,
        public string $notificationType,
        public NotificationContext $context,
        public array $channels = [],
    ) {}

    public function handle(): void
    {
        Log::info('SendNotificationJob: start', [
            'document_id' => $this->context->documentId,
            'recipients_count' => count($this->recipients),
            'notification_type' => $this->notificationType,
            'channels' => $this->channels
        ]);

        if (empty($this->recipients)) {
            Log::info('SendNotificationJob: No recipients to process');
            return;
        }

        if (empty($this->channels)) {
            Log::warning('SendNotificationJob: No channels configured, using default', [
                'document_id' => $this->context->documentId,
                'notification_type' => $this->notificationType
            ]);
            $this->channels = ['mail', 'database']; // Fallback to default channels
        }

        try {
            // Batch-load all User models for better performance
            $recipientIds = collect($this->recipients)->pluck('id')->filter()->toArray();
            $users = User::whereIn('id', $recipientIds)->get()->keyBy('id');

            Log::info('SendNotificationJob: Users loaded from database', [
                'requested_count' => count($recipientIds),
                'loaded_count' => $users->count()
            ]);

            foreach ($this->recipients as $recipient) {
                $this->sendNotificationToRecipient($recipient, $users);
            }

            Log::info('SendNotificationJob: All notifications sent successfully', [
                'document_id' => $this->context->documentId,
                'notification_type' => $this->notificationType,
                'recipient_count' => count($this->recipients)
            ]);

        } catch (\Throwable $e) {
            Log::error('SendNotificationJob: Failed to send notifications', [
                'document_id' => $this->context->documentId,
                'notification_type' => $this->notificationType,
                'recipient_count' => count($this->recipients),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e; // Re-throw to trigger retry mechanism
        }
    }


    /**
     * Send notification to a single recipient
     */
    protected function sendNotificationToRecipient(array $recipient, $users): void
    {
        try {
            Log::info('SendNotificationJob: Sending notification to recipient', [
                'recipient_id' => $recipient['id'],
                'recipient_email' => $recipient['email'] ?? 'not_set',
                'notification_type' => $this->notificationType,
                'channels' => $this->channels
            ]);

            // Validate recipient data
            if (empty($recipient['id'])) {
                Log::warning('SendNotificationJob: Recipient missing ID', ['recipient' => $recipient]);
                return;
            }

            // Get the actual User model from the pre-loaded collection
            $user = $users->get($recipient['id']);

            if (!$user) {
                Log::warning('SendNotificationJob: User not found in database', [
                    'recipient_id' => $recipient['id'],
                    'recipient_email' => $recipient['email'] ?? 'not_set'
                ]);
                return;
            }

            // Create notification instance
            $notification = new DocumentWorkflowNotification(
                context: $this->context,
                recipient: $recipient,
                notificationType: $this->notificationType,
                channels: $this->channels
            );

            // Send notification using the real User model
            $user->notify($notification);

            Log::info('SendNotificationJob: Notification sent successfully', [
                'recipient_id' => $recipient['id'],
                'recipient_email' => $user->email ?? ($recipient['email'] ?? 'not_set'),
                'notification_type' => $this->notificationType,
                'channels' => $this->channels
            ]);

        } catch (\Throwable $e) {
            Log::error('SendNotificationJob: Failed to send notification to recipient', [
                'recipient_id' => $recipient['id'],
                'recipient_email' => $recipient['email'] ?? 'not_set',
                'notification_type' => $this->notificationType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Don't re-throw here to allow other recipients to be processed
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $e): void
    {
        Log::error('SendNotificationJob: failed', [
            'document_id' => $this->context->documentId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}
