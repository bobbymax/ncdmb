<?php

namespace App\Notifications;

use App\DTOs\ResourceNotificationContext;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResourceActionNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    public function __construct(public ResourceNotificationContext $context) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        return [
            'resource_type' => $this->context->resourceType,
            'resource_id' => $this->context->resourceId,
            'action' => $this->context->action,
            'actor_id' => $this->context->actorId,
            'resource_data' => $this->context->resourceData,
            'metadata' => $this->context->metadata,
            'url' => $this->context->getResourceUrl(),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id ?? uniqid(),
            'type' => 'resource_action',
            'resource_type' => $this->context->resourceType,
            'resource_id' => $this->context->resourceId,
            'action' => $this->context->action,
            'data' => $this->toArray($notifiable),
            'created_at' => now()->toISOString(),
        ]);
    }

    public function broadcastOn($notifiable): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $notifiable->id)
        ];
    }

    public function broadcastAs(): string
    {
        return 'NewNotification';
    }
}

