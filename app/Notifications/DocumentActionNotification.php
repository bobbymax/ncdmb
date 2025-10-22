<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentActionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public array $payload)
    {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        // choose channels per notifiable or config; keep 'database' for persistence
        $channels = ['database'];

        // optionally broadcast
        if (!empty($this->payload['meta']['broadcast'] ?? false)) {
            $channels[] = 'broadcast';
        }

        return $channels;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        // small, queryable payload stored in notifications table
        return [
            'document_id' => $this->payload['document']['id'] ?? null,
            'title' => $this->payload['document']['title'] ?? null,
            'action' => $this->payload['action'] ?? null,
            'actor_id' => $this->payload['actor_id'] ?? null,
            'meta' => $this->payload['meta'] ?? [],
        ];
    }

    // broadcast payload (optional)
    public function toBroadcast($notifiable)
    {
        return new \Illuminate\Notifications\Messages\BroadcastMessage($this->toArray($notifiable));
    }
}
