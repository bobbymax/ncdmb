<?php

namespace App\Jobs;

use App\DTOs\ResourceNotificationContext;
use App\Events\ResourceNotificationProgress;
use App\Mail\ResourceActionMail;
use App\Models\User;
use App\Notifications\ResourceActionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendResourceNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public int $tries = 3;
    public array $backoff = [5, 15, 30];

    public function __construct(
        public ResourceNotificationContext $context,
        public User $user,
        public int $position
    ) {}

    public function handle(): void
    {
        Log::info('SendResourceNotificationJob: Sending', [
            'user_id' => $this->user->id,
            'user_email' => $this->user->email,
            'resource_type' => $this->context->resourceType,
            'action' => $this->context->action
        ]);

        try {
            // Send email
            Mail::to($this->user->email)
                ->queue(new ResourceActionMail($this->context, $this->user));

            // Store database notification
            $this->user->notify(new ResourceActionNotification($this->context));

            // Broadcast progress
            broadcast(new ResourceNotificationProgress(
                $this->context->resourceType,
                $this->context->resourceId,
                $this->position,
                count($this->context->recipients),
                "Sent to {$this->user->name}"
            ));

            Log::info('SendResourceNotificationJob: Success', [
                'user_id' => $this->user->id,
                'position' => $this->position
            ]);

        } catch (\Throwable $e) {
            Log::error('SendResourceNotificationJob: Failed', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}

