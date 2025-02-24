<?php

namespace App\Mail;

use App\Models\Document;
use App\Models\DocumentAction;
use App\Models\DocumentDraft;
use App\Models\ProgressTracker;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkflowNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Document $document;
    public DocumentDraft $lastDraft;
    public DocumentAction $documentAction;
    public ProgressTracker $progressTracker;
    public User $user;

    /**
     * Create a new message instance.
     */
    public function __construct(
        Document $document,
        DocumentAction $documentAction,
        ProgressTracker $progressTracker,
        User $user
    ) {
        $this->document = $document;
        $this->documentAction = $documentAction;
        $this->progressTracker = $progressTracker;
        $this->user = $user;
        $this->lastDraft = $document->drafts()->latest()->first();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Workflow Notification',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notifications.workflow_notification',
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
