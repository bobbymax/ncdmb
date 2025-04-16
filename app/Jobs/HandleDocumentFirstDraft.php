<?php

namespace App\Jobs;

use App\Mail\DocumentWorkflowNotification;
use App\Models\Document;
use App\Models\User;
use App\Models\Workflow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class HandleDocumentFirstDraft implements ShouldQueue
{
    use Queueable;

    public User $user;
    public Document $document;
    public Workflow $workflow;

    /**
     * Create a new job instance.
     */
    public function __construct(Document $document, User $user)
    {
        $this->user = $user;
        $this->document = $document;
        $this->workflow = $document->workflow;
    }

    /**
     * Ensure the database connection is alive before executing.
     */
    protected function ensureDbConnection(): void
    {
        try {
            DB::connection()->getPdo(); // Test current PDO connection
        } catch (\Exception $e) {
            DB::reconnect(); // Force reconnection if it's dead
        }
    }

    /**
     * Execute the job.
     * @throws \Exception
     */
    public function handle(): void
    {
        $this->ensureDbConnection();

        try {
            DB::transaction(function () {
                $this->processFirstDraft();
            });
        } catch (\Exception $e) {
            Log::error("Workflow processing failed for document ID: {$this->document->id} - " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @throws \Exception
     */
    protected function processFirstDraft(): void
    {
        // Get first stage of progress tracker
        $firstStageTracker = $this->workflow->trackers->firstWhere('order', 1);

        if (!$firstStageTracker) {
            throw new \Exception('Workflow tracker not found');
        }

        $emailLists = $firstStageTracker->recipients;

        if ($emailLists->isEmpty()) {
            Log::warning("No recipients found for workflow stage ID: {$firstStageTracker->id}");
        }

        $this->document->update([
            'progress_tracker_id' => $firstStageTracker->id,
        ]);

        $draft = $this->prepare($firstStageTracker);

        $this->notifyRecipients($draft, $firstStageTracker);
    }

    private function notifyRecipients($draft, $firstStageTracker): void
    {
        foreach ($firstStageTracker->recipients as $recipient) {
            $departmentId = $recipient->department_id > 0 ? $recipient->department_id : $this->user->department_id;
            try {
                $staff = $recipient->group->users->firstWhere('department_id', $departmentId);
                $email = $staff?->email ?? config('mail.fallback_email', 'fallback@example.com');

                Mail::to($email)->queue(new DocumentWorkflowNotification($draft, $firstStageTracker, "First Stage"));
                Log::info("Notification sent to: {$email} for document ID: {$this->document->id}");
            } catch (\Exception $e) {
                Log::error("Failed to send notification for recipient ID: {$recipient->id} - " . $e->getMessage());
            }
        }
    }

    private function prepare($tracker)
    {
        return $this->document->drafts()->create([
            'document_id' => $this->document->id,
            'document_type_id' => $this->document->document_type_id,
            'group_id' => $tracker->stage->group_id,
            'created_by_user_id' => $this->user->id,
            'progress_tracker_id' => $tracker->id,
            'current_workflow_stage_id' => $tracker->stage->id,
            'department_id' => $tracker->stage->department_id > 0 ? $tracker->stage->department_id : $this->document->department_id,
            'document_draftable_id' => $this->document->documentable_id,
            'document_draftable_type' => $this->document->documentable_type,
            'status' => $this->document->status,
        ]);
    }
}
