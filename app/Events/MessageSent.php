<?php

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageSent implements ShouldBroadcast
{
    /**
     * Create a new event instance.
     */
    public function __construct(public Conversation $conversation)
    {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('threads.'.$this->conversation->thread_id)
        ];
    }

    public function broadcastAs(): string
    {
        return 'MessageSent';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->conversation->id,
            'thread_id' => $this->conversation->thread_id,
            'sender_id' => $this->conversation->sender_id,
            'message' => $this->conversation->message,
            'replies' => $this->conversation->replies,
            'attachments' => $this->conversation->attachments,
            'category' => $this->conversation->category,
            'is_pinned' => $this->conversation->is_pinned,
            'is_delivered' => $this->conversation->is_delivered,
            'marked_as_read' => $this->conversation->marked_as_read,
//            'read_at' => $this->conversation->read_at?->toISOString(),
            'created_at' => $this->conversation->created_at->toISOString(),
        ];
    }
}
