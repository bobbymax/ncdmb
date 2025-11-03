<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResourceNotificationProgress implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $resourceType,
        public int $resourceId,
        public int $current,
        public int $total,
        public string $message,
        public bool $error = false
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("resource.{$this->resourceType}.{$this->resourceId}")
        ];
    }

    public function broadcastAs(): string
    {
        return 'ResourceNotificationProgress';
    }

    public function broadcastWith(): array
    {
        return [
            'resource_type' => $this->resourceType,
            'resource_id' => $this->resourceId,
            'current' => $this->current,
            'total' => $this->total,
            'percentage' => $this->total > 0 ? round(($this->current / $this->total) * 100) : 0,
            'message' => $this->message,
            'error' => $this->error,
            'completed' => $this->current >= $this->total && !$this->error,
            'timestamp' => now()->toISOString(),
        ];
    }
}

