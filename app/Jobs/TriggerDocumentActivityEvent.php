<?php

namespace App\Jobs;

use App\DTO\DocumentActivityContext;
use App\Events\DocumentActionPerformed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimitedWithRedis;
use Illuminate\Queue\SerializesModels;

class TriggerDocumentActivityEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $queue   = 'notifications';
    public int $tries   = 5;
    public array $backoff = [10, 60, 120];

    /**
     * Create a new job instance.
     */
    public function __construct(public DocumentActivityContext $context) {}

    public function uniqueId(): string
    {
        $c = $this->context;
        return "doc:{$c->document_id}|stage:{$c->workflow_stage_id}|action:{$c->action_performed}";
    }

    // throttling (optional)
    public function middleware(): array
    {
        return [ new RateLimitedWithRedis('document-notify') ];
    }

    public function tags(): array
    {
        $c = $this->context;
        return ["document:{$c->document_id}", "service:{$c->service}", "action:{$c->action_performed}"];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        event(new DocumentActionPerformed($this->context));
    }
}
