<?php

namespace App\Notifications;

use App\DTO\DocumentActivityContext;
use App\Models\NotificationPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentActivityNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected string $subject,
        protected array $lines,
        protected array $payloadForDatabase
    ) {
        $this->onQueue('emails');
    }

    public static function fromContext(DocumentActivityContext $ctx, array $audTags): self
    {
        $builder = app(MessageBuilder::class);
        $subject = $builder->subject($ctx, $audTags);
        $lines   = $builder->lines($ctx, $audTags);

        return new self($subject, $lines, [
            'document_id'      => $ctx->document_id,
            'action'           => $ctx->action_performed,
            'workflow_stage_id'=> $ctx->workflow_stage_id,
            'tags'             => $audTags,
            'service'          => $ctx->service,
            'title'            => $ctx->document_title,
            'ref'              => $ctx->document_ref,
        ]);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $s = $notifiable->notificationPreference;
        if (!$s) return ['mail', 'database'];

        $channels = [];
        if ($s->via_mail)     $channels[] = 'mail';
        if ($s->via_database) $channels[] = 'database';

        // Optional: honor mutes (action- or service-scoped)
        $mutes = collect($s->mutes ?? []);
        $key   = "{$this->payloadForDatabase['service']}.{$this->payloadForDatabase['action_performed']}";
        if ($mutes->contains($key)) {
            $channels = []; // muted → no sends
        }
        return $channels;
    }

    public function tags(): array
    {
        return [
            "document:{$this->payloadForDatabase['document_id']}",
            "service:{$this->payloadForDatabase['service']}",
            "action:{$this->payloadForDatabase['action_performed']}",
            ...$this->payloadForDatabase['tags'],
        ];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'document_id'       => $this->payloadForDatabase['document_id'],
            'action'            => $this->payloadForDatabase['action_performed'],
            'workflow_stage_id' => $this->payloadForDatabase['workflow_stage_id'],
            'tags'              => $this->payloadForDatabase['tags'], // audience roles
            'service'           => $this->payloadForDatabase['service'],
            'title'             => $this->payloadForDatabase['document_title'],
            'ref'               => $this->payloadForDatabase['document_ref'],
            'subject'           => $this->subject,
            'preview'           => implode(' ', array_slice($this->lines, 0, 2)), // small snippet for UI
            'url'               => route('documents.show', $this->payloadForDatabase['document_id']),
            'sent_at'           => now(),
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->markdown('emails.documents.notifications', [
                'lines' => $this->lines,
                'ctaUrl' => route('documents.show', $this->payloadForDatabase['document_id']),
            ]);
    }

    public function toArray($notifiable): array
    {
        return $this->payloadForDatabase;
    }
}
