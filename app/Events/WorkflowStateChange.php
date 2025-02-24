<?php

namespace App\Events;

use App\Models\Document;
use App\Models\DocumentAction;
use App\Models\ProgressTracker;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkflowStateChange
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Document $document;
    public DocumentAction $documentAction;
    public int $userId;
    public ProgressTracker $previousTracker;
    public ?ProgressTracker $nextTracker;

    /**
     * Create a new event instance.
     */
    public function __construct(
        Document $document,
        DocumentAction $documentAction,
        int $userId,
        ProgressTracker $previousTracker,
        ?ProgressTracker $nextTracker = null
    ) {
        $this->document = $document;
        $this->documentAction = $documentAction;
        $this->previousTracker = $previousTracker;
        $this->nextTracker = $nextTracker;
        $this->userId = $userId;
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
