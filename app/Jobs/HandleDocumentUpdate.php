<?php

namespace App\Jobs;

use App\Mail\DocumentUpdateNotification;
use App\Models\Document;
use App\Models\DocumentAction;
use App\Models\DocumentUpdate;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class HandleDocumentUpdate implements ShouldQueue
{
    use Queueable;

    protected Document $document;
    protected User $user;
    protected DocumentAction $documentAction;
    protected DocumentUpdate $documentUpdate;

    /**
     * Create a new job instance.
     */
    public function __construct(
        Document $document,
        DocumentAction $documentAction,
        DocumentUpdate $documentUpdate
    ) {
        $this->document = $document;
        $this->user = $document->user;
        $this->documentAction = $documentAction;
        $this->documentUpdate = $documentUpdate;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->user->email)->queue(new DocumentUpdateNotification($this->document, $this->user, $this->documentAction, $this->documentUpdate));
    }
}
