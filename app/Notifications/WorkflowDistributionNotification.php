<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkflowDistributionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Document $document;
    protected int $documentActionId;
    protected string $audience;
    protected string $name;

    public function __construct(
        Document $document,
        int $documentActionId,
        string $audience = 'linked_distribution',
        string $name = 'Colleague'
    ) {
        $this->document = $document;
        $this->audience = $audience;
        $this->name = $name;
        $this->documentActionId = $documentActionId;

        $this->onQueue($this->resolveQueue($audience));
    }

    protected function resolveQueue(string $audience): string
    {
        return match ($audience) {
            'authorising_officer', 'current_distribution' => 'notifications-high',
            'next_distribution', 'initiator' => 'notifications-medium',
            default => 'notifications-low',
        };
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return match ($this->audience) {
            'initiator' => $this->toInitiatorMail(),
            'authorising_officer' => $this->toAuthorisingOfficerMail(),
            'linked_initiator' => $this->toLinkedInitiatorsMail(),
            'current_distribution' => $this->toCurrentDistributionMail(),
            'next_distribution' => $this->toNextDistributionMail(),
            default => (new MailMessage)
                ->subject('Document Workflow Notification')
                ->line('A document update has occurred. Further details will be provided by your administrator.'),
        };
    }

    protected function getResource(
        int|string|array $value,
        string $service
    ) {
        return processor()->resourceResolver($value, $service);
    }

    /**
     * Message for Draft Initiator.
     */
    protected function toAuthorisingOfficerMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('Action Successfully Executed on ' . $this->document->title)
            ->greeting('Dear ' . $this->name . ',')
            ->line('We acknowledge the successful execution of your recent action on the document titled "' . $this->document->title . '", currently situated at a stage within the workflow.')
            ->line('**Action Summary:**')
            ->line('- Document Type: ' . optional($this->document->documentType)->name)
            ->line('- Action Performed: ' . optional($this->getResource($this->documentActionId, 'documentaction'))->name)
            ->line('Your contribution to the timely progression of this process is highly appreciated.')
            ->line('Kindly remain attentive to subsequent notifications as the document advances.');
    }

    /**
     * Message for Authorising Officer (staff that handled the last draft).
     */
    protected function toInitiatorMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('Notification: Update on Document You Recently Authorized')
            ->greeting('Dear ' . $this->name . ',')
            ->line('Please be informed that the document you recently authorized — titled "' . $this->document->title . '" — has progressed with a new action.')
            ->line('**Details of the Update:**')
            ->line('- Document Type: ' . optional($this->document->documentType)->name)
            ->line('- Recent Action: ' . optional($this->getResource($this->documentActionId, 'documentaction'))->name)
            ->line('Your previous authorization enabled this update. This message is to keep you informed of its continued progression.')
            ->line('No immediate action is required from your end, but feel free to review the latest state of the document for reference.');
    }

    /**
     * Message for Linked Document Initiators.
     */
    protected function toLinkedInitiatorsMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('Notification of Activity on Linked Document: ' . $this->document->title)
            ->greeting('Dear ' . $this->name . ',')
            ->line('Please be informed that an action has been executed on a document linked to your previous submission, titled "' . $this->document->title . '".')
            ->line('**Activity Details:**')
            ->line('This development may influence the processing of related workflows. Kindly remain available for any further correspondence or action that may be required.');
    }

    /**
     * Message for Current Progress Tracker Distribution List.
     */
    protected function toCurrentDistributionMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('Workflow Update: Document Activity')
            ->greeting('Dear ' . $this->name . ',')
            ->line('A document under your purview within the stage has recently undergone an update.')
            ->line('**Document Overview:**')
            ->line('- Title: ' . $this->document->title)
            ->line('- Type: ' . optional($this->document->documentType)->name)
            ->line('- Recent Action: ' . optional($this->getResource($this->documentActionId, 'documentaction'))->name)
            ->line('Kindly review the updated document and, where necessary, take the appropriate steps to facilitate the continued progression of the workflow.');
    }

    /**
     * Message for Next Progress Tracker Distribution List.
     */
    protected function toNextDistributionMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('Advisory: Document Progress')
            ->greeting('Dear ' . $this->name . ',')
            ->line('Kindly be advised that a document is approaching your assigned workflow stage, following an action completed at the current stage.')
            ->line('**Document Synopsis:**')
            ->line('- Document Title: ' . $this->document->title)
            ->line('- Document Type: ' . optional($this->document->documentType)->name)
            ->line('- Last Recorded Action: ' . optional($this->getResource($this->documentActionId, 'documentaction'))->name)
            ->line('You are encouraged to prepare for its arrival and review the relevant details in advance to ensure a seamless transition upon receipt.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
