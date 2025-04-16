<?php

namespace App\Jobs;

use App\Mail\DocumentWorkflowNotification;
use App\Models\Department;
use App\Models\Document;
use App\Models\User;
use App\Models\Workflow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class HandleDocumentWorkflow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public User $user;
    public Document $document;
    public string $signature;
    public int $draftable_id;
    public string $draftable_type;
    public Department $department;
    public Workflow $workflow;
    public string $action;
    public ?int $targetStageOrder;
    public string $status;
    /**
     * Create a new job instance.
     */
    public function __construct(
        User $user,
        Document $document,
        string $action,
        ?int $targetStageOrder = null,
        string $status = "",
        string $signature = ""
    ) {
        $this->user = $user;
        $this->document = $document->load(['department', 'documentType']);
        $this->draftable_id = $document->documentable_id;
        $this->draftable_type = $document->documentable_type;
        $this->department = $document->department;
        $this->workflow = $document->workflow;
        $this->signature = $signature;
        $this->action = $action;
        $this->targetStageOrder = $targetStageOrder;
        $this->status = $status;
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
                $this->processWorkflow();
            });
        } catch (\Exception $e) {
            Log::error("Workflow processing failed for document ID: {$this->document->id} - " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process the workflow based on the action.
     * @throws \Exception
     */
    private function processWorkflow(): void
    {
        switch ($this->action) {
            case 'first':
                $this->processStage(1, "first", true);
                break;

            case 'next':
                // Change this section
                $currentStageOrder = $this->document->current_tracker->order ?? 0;
                $this->processStage($currentStageOrder + 1, "next");
                break;

            case 'goto':
                if (!$this->targetStageOrder) {
                    throw new \Exception("Target stage order is required for 'goto-stage' action.");
                }
                $this->processStage($this->targetStageOrder, "goto");
                break;

            case 'pause':
                $this->pauseProcess();
                break;

            case 'reverse':
                if (!$this->targetStageOrder) {
                    throw new \Exception("Target stage order is required for 'rollback-process' action.");
                }
                $this->rollbackProcess($this->targetStageOrder);
                break;

            case 'stall':
                $this->stallStage();
                break;

            case 'close':
                $this->endProcess();
                break;

            default:
                throw new \Exception("Invalid workflow action: {$this->action}");
        }
    }

    /**
     * Process a specific stage by its order.
     */
    private function processStage(int $stageOrder, string $stageName, bool $autoTriggerNext = false): void
    {
        $stageTracker = $this->workflow->trackers->firstWhere('order', $stageOrder);

        if (!$stageTracker) {
            throw new \Exception("Stage with order {$stageOrder} is missing for workflow ID: {$this->workflow->id}");
        }

        $recipients = $stageTracker->stage->recipients;

        if ($recipients->isEmpty()) {
            Log::warning("No recipients found for workflow stage ID: {$stageTracker->id}");
        }

        $this->document->update([
            'progress_tracker_id' => $stageTracker->id,
        ]);

        $draft = $this->createDraft($stageTracker);

        $this->notifyRecipients($draft, $stageTracker, $stageName);

        // Automatically trigger the next stage if applicable
        if ($autoTriggerNext) {
            $this->triggerNextStage($stageOrder);
        }
    }

    /**
     * Trigger the next stage in the workflow if applicable.
     */
    private function triggerNextStage(int $currentStageOrder): void
    {
        // Only trigger the next stage if the current stage is the first stage
        if ($currentStageOrder === 1) {
            $nextStageOrder = $currentStageOrder + 1;
            $nextStageTracker = $this->workflow->trackers->firstWhere('order', $nextStageOrder);

            if ($nextStageTracker) {
                Log::info("Automatically triggering next stage (order: {$nextStageOrder}) for document ID: {$this->document->id}");
                self::dispatch($this->user, $this->document, 'next');
            } else {
                Log::info("No further stages found. Workflow may be complete for document ID: {$this->document->id}");
            }
        }
    }

    /**
     * Pause the workflow process.
     */
    private function pauseProcess(): void
    {
        $this->document->update([
            'status' => 'paused',
        ]);

        Log::info("Document ID {$this->document->id} workflow paused.");
    }

    /**
     * Roll back the workflow to a previous stage.
     */
    private function rollbackProcess(int $stageOrder): void
    {
        $stageTracker = $this->workflow->trackers->firstWhere('order', $stageOrder);

        if (!$stageTracker) {
            throw new \Exception("Stage with order {$stageOrder} is missing for workflow ID: {$this->workflow->id}");
        }

        $this->document->update([
            'progress_tracker_id' => $stageTracker->id,
            'status' => 'reversed',
        ]);

        Log::info("Document ID {$this->document->id} workflow rolled back to stage order {$stageOrder}.");
        $this->notifyRecipients(null, $stageTracker, "rollback");
    }

    /**
     * Mark the document and workflow as stalled.
     */
    private function stallStage(): void
    {
        $this->document->update([
            'status' => 'stalled',
        ]);

        Log::info("Document ID {$this->document->id} workflow stalled.");
    }

    /**
     * Mark the document and workflow as completed.
     */
    private function endProcess(): void
    {
        $this->document->update([
            'status' => 'completed',
        ]);

        $this->workflow->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        Log::info("Document ID {$this->document->id} workflow completed.");
//        $this->notifyCompletion();
    }

    /**
     * Notify recipients of the workflow stage.
     */
    private function notifyRecipients($draft, $stageTracker, string $stageName): void
    {
        foreach ($stageTracker->stage->recipients as $recipient) {
            try {
                $staff = $recipient->users->firstWhere('department_id', $this->department->id);
                $email = $staff?->email ?? config('mail.fallback_email', 'fallback@example.com');

                Mail::to($email)->queue(new DocumentWorkflowNotification($draft, $stageTracker, $stageName));
                Log::info("Notification sent to: {$email} for document ID: {$this->document->id}");
            } catch (\Exception $e) {
                Log::error("Failed to send notification for recipient ID: {$recipient->id} - " . $e->getMessage());
            }
        }
    }

    /**
     * Create a draft for the given stage.
     */
    private function createDraft($stage)
    {
        return $this->document->drafts()->create([
            'document_id' => $this->document->id,
            'group_id' => $stage->stage->group_id,
            'created_by_user_id' => $this->user->id,
            'progress_tracker_id' => $stage->id,
            'current_workflow_stage_id' => $stage->stage->id,
            'department_id' => $stage->stage->department_id > 0 ? $stage->stage->department_id : $this->department->id,
            'document_draftable_id' => $this->draftable_id,
            'document_draftable_type' => $this->draftable_type,
            'status' => $this->status,
            'signature' => $this->signature,
        ]);
    }

}
