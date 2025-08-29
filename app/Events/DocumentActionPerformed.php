<?php

namespace App\Events;

use App\DTO\DocumentActivityContext;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentActionPerformed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public bool $afterCommit = true; // ensures listeners run post-commit

    /**
     * Create a new event instance.
     */
    public function __construct(public DocumentActivityContext $context) {}
}
