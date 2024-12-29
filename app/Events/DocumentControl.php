<?php

namespace App\Events;

use App\Models\Document;
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

    public Document $document;
    public int $draftable_id;
    public string $draftable_type;

    /**
     * Create a new event instance.
     */
    public function __construct(Document $document, int $draftable_id, string $draftable_type)
    {
        $this->document = $document;
        $this->draftable_id = $draftable_id;
        $this->draftable_type = $draftable_type;
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
