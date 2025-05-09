<?php

namespace App\Jobs;

use App\Models\Document;
use App\Notifications\WorkflowDistributionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class DispatchNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $email;
    public string $name;
    public int $actionId;
    public string $type;
    public Document $document;

    /**
     * Create a new job instance.
     */
    public function __construct(
        Document $document,
        string $email,
        string $name,
        string $type,
        int $actionId
    ) {
        $this->email = $email;
        $this->name = $name;
        $this->type = $type;
        $this->document = $document;
        $this->actionId = $actionId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!$this->email) return;

        Notification::route('mail', $this->email)
            ->notify(new WorkflowDistributionNotification(
                $this->document,
                $this->actionId,
                $this->type,
                $this->name
            ));
    }
}
