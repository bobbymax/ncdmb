<?php

namespace App\Notifications;

use App\DTOs\NotificationContext;
use App\Services\NotificationTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
//use Illuminate\Notifications\Messages\SmsMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class DocumentWorkflowNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public NotificationContext $context,
        public array $recipient,
        public string $notificationType,
        public array $channels = ['mail', 'database']
    ) {}

    /**
     * Get the notification's delivery channels
     */
    public function via($notifiable): array
    {
        Log::info('DocumentWorkflowNotification: via() method called', [
            'channels' => $this->channels,
            'recipient_id' => $this->recipient['id'] ?? 'unknown',
            'notification_type' => $this->notificationType
        ]);

        return $this->channels;
    }

    /**
     * Get the mail representation of the notification
     */
    public function toMail($notifiable): MailMessage
    {
        Log::info('DocumentWorkflowNotification: toMail() method called', [
            'recipient_id' => $this->recipient['id'] ?? 'unknown',
            'recipient_email' => $this->recipient['email'] ?? 'not_set',
            'notification_type' => $this->notificationType
        ]);

        $templateService = app(NotificationTemplateService::class);
        $template = $templateService->processTemplate($this->notificationType, [
            'template_variables' => $this->getTemplateVariables()
        ]);

        $mailMessage = (new MailMessage)
            ->subject($template['subject'])
            ->greeting($template['greeting'])
            ->line($template['body'])
            ->action($template['action_text'], $template['action_url'])
            ->line($template['footer']);

        Log::info('DocumentWorkflowNotification: Mail message built', [
            'subject' => $template['subject'],
            'recipient_email' => $this->recipient['email'] ?? 'not_set'
        ]);

        return $mailMessage;
    }

    /**
     * Get the database representation of the notification
     */
    public function toDatabase($notifiable): array
    {
        Log::info('DocumentWorkflowNotification: toDatabase() method called', [
            'recipient_id' => $this->recipient['id'] ?? 'unknown',
            'notification_type' => $this->notificationType
        ]);

        try {
            $document = processor()->resourceResolver($this->context->documentId, 'document');
            
            if (!$document) {
                throw new \RuntimeException('Document not found');
            }

            Log::info('DocumentWorkflowNotification: Document resolved successfully', [
                'document_id' => $this->context->documentId,
                'document_ref' => $document->ref ?? 'N/A',
                'document_title' => $document->title ?? 'N/A'
            ]);

            return [
                'type' => $this->notificationType,
                'document_id' => $this->context->documentId,
                'document_ref' => $document->ref ?? 'N/A',
                'document_title' => $document->title ?? 'N/A',
                'tracker_identifier' => $this->context->currentTracker['identifier'] ?? '',
                'action_status' => $this->context->actionStatus,
                'logged_in_user' => $this->context->loggedInUser,
                'recipient_type' => $this->recipient['type'] ?? 'unknown',
                'created_at' => now()
            ];

        } catch (\Throwable $e) {
            Log::error('DocumentWorkflowNotification: Failed to resolve document', [
                'document_id' => $this->context->documentId,
                'recipient_id' => $this->recipient['id'] ?? 'unknown',
                'notification_type' => $this->notificationType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return safe fallback data
            return [
                'type' => $this->notificationType,
                'document_id' => $this->context->documentId,
                'document_ref' => 'N/A',
                'document_title' => 'Document Not Found',
                'tracker_identifier' => $this->context->currentTracker['identifier'] ?? '',
                'action_status' => $this->context->actionStatus,
                'logged_in_user' => $this->context->loggedInUser,
                'recipient_type' => $this->recipient['type'] ?? 'unknown',
                'created_at' => now(),
                'error' => 'Document resolution failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get the SMS representation of the notification
     */
//    public function toSms($notifiable): SmsMessage
//    {
//        Log::info('DocumentWorkflowNotification: toSms() method called', [
//            'recipient_id' => $this->recipient['id'] ?? 'unknown',
//            'recipient_phone' => $this->recipient['phone'] ?? 'not_set',
//            'notification_type' => $this->notificationType
//        ]);
//
//        $templateService = app(NotificationTemplateService::class);
//        $template = $templateService->processTemplate($this->notificationType, [
//            'template_variables' => $this->getTemplateVariables()
//        ]);
//
//        // Create SMS-friendly message
//        $smsBody = "{$template['subject']}\n\n{$template['body']}\n\n{$template['action_url']}";
//
//        return (new SmsMessage)
//            ->content($smsBody);
//
//        Log::info('DocumentWorkflowNotification: SMS message built', [
//            'recipient_phone' => $this->recipient['phone'] ?? 'not_set'
//        ]);
//    }

    /**
     * Get template variables for string interpolation
     */
    protected function getTemplateVariables(): array
    {
        $variables = $this->context->getTemplateVariables();

        // Add recipient-specific variables
        $variables['recipient_name'] = $this->getRecipientName();

        return $variables;
    }

    /**
     * Get the recipient's name for the notification
     */
    protected function getRecipientName(): string
    {
        $firstname = $this->recipient['firstname'] ?? '';
        $surname = $this->recipient['surname'] ?? '';

        return trim("{$firstname} {$surname}") ?: 'User';
    }
}
