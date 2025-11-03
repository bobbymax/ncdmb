<?php

namespace App\Mail;

use App\DTOs\ResourceNotificationContext;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResourceActionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ResourceNotificationContext $context,
        public User $user
    ) {}

    public function envelope(): Envelope
    {
        $actionText = ucfirst($this->context->action);
        $resourceText = ucfirst(str_replace('_', ' ', $this->context->resourceType));

        return new Envelope(
            subject: "{$resourceText} {$actionText} - Action Required",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.resource-action',
            with: [
                'userName' => $this->user->name,
                'resourceType' => ucfirst(str_replace('_', ' ', $this->context->resourceType)),
                'action' => ucfirst($this->context->action),
                'resourceData' => $this->context->resourceData,
                'metadata' => $this->context->metadata,
                'resourceUrl' => $this->context->getResourceUrl(),
            ]
        );
    }
}

