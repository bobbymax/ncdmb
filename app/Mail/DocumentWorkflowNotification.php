<?php

namespace App\Mail;

use App\Models\DocumentDraft;
use App\Models\ProgressTracker;
use App\Models\WorkflowStage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentWorkflowNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public DocumentDraft $draft;
    public ProgressTracker $progressTracker;
    public string $mode;

    /**
     * Create a new message instance.
     */
    public function __construct(
        DocumentDraft $draft,
        ProgressTracker $progressTracker,
        string $mode = "next"
    ) {
        $this->draft = $draft;
        $this->progressTracker = $progressTracker;
        $this->mode = $mode;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Document Tracking Notification: REF- {$this->draft->document->ref}.",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.workflow.notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
