<?php

namespace App\Mail;

use App\Models\Document;
use App\Models\DocumentAction;
use App\Models\DocumentUpdate;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentUpdateNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Document $document;
    public User $user;
    public DocumentAction $documentAction;
    public DocumentUpdate $documentUpdate;

    /**
     * Create a new message instance.
     */
    public function __construct(
        Document $document,
        User $user,
        DocumentAction $documentAction,
        DocumentUpdate $documentUpdate
    ) {
        $this->document = $document;
        $this->user = $user;
        $this->documentAction = $documentAction;
        $this->documentUpdate = $documentUpdate;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Document Update Notification!!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.documents.updates',
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
