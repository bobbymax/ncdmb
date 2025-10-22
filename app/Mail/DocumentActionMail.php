<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentActionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public $recipientUser, public array $payload)
    {}

    public function build()
    {
        // use a simple view or markdown mail template
        $subject = $this->payload['document']['title'] ?? 'Document';
        return $this->subject("Document update: $subject")
            ->markdown('notifications.workflow.actions', [
                'user' => $this->recipientUser,
                'payload' => $this->payload,
            ]);
    }
}
