<?php

namespace App\Events;

use App\Models\Document;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentControl
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public Document $document;
    public string $action;
    public ?int $targetStageOrder;
    public string $status;

    /**
     * Create a new event instance.
     */
    public function __construct(
        Document $document,
        string $action,
        ?int $targetStageOrder = null,
        string $status = ""
    ) {
        $this->user = $document->user;
        $this->document = $document;
        $this->action = $action;
        $this->targetStageOrder = $targetStageOrder;
        $this->status = $status;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
