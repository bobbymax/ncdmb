<?php

namespace App\Jobs;

use App\Mail\DocumentWorkflowNotification;
use App\Models\Department;
use App\Models\Document;
use App\Models\Workflow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class HandleDocumentWorkflow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Document $document;
    public int $draftable_id;
    public string $draftable_type;
    public Department $department;
    public Workflow $workflow;
    /**
     * Create a new job instance.
     */
    public function __construct(Document $document)
    {
        $this->document = $document->load(['department', 'documentType.workflow.workflowStages.recipients.users']);
        $this->draftable_id = $document->documentable_id;
        $this->draftable_type = $document->documentable_type;
        $this->department = $document->department;
        $this->workflow = $document->documentType->workflow;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            DB::transaction(function () {
                // Validate workflow and stages
                $firstStage = $this->workflow->workflowStages->firstWhere('order', 1);
                if (!$firstStage) {
                    Log::error("Workflow first stage not found for workflow ID: {$this->workflow->id}");
                    throw new \Exception("First stage of workflow is missing.");
                }

                $recipients = $firstStage->recipients;
                if ($recipients->isEmpty()) {
                    Log::warning("No recipients found for workflow stage ID: {$firstStage->id}");
                }

                // Create the draft
                $firstDraft = [
                    'document_id' => $this->document->id,
                    'group_id' => $firstStage->group_id,
                    'created_by_user_id' => $this->document->user_id,
                    'current_workflow_stage_id' => $firstStage->id,
                    'department_id' => $firstStage->department_id < 1 ? $this->department->id : $firstStage->department_id,
                    'document_draftable_id' => $this->draftable_id,
                    'document_draftable_type' => $this->draftable_type,
                    'status' => $firstStage->name,
                ];

                $firstDraftRecord = $this->document->drafts()->create($firstDraft);
                Log::info("Draft created for document ID: {$this->document->id}, stage: {$firstStage->name}");

                // Notify recipients
                foreach ($recipients as $recipient) {
                    try {
                        $staff = $recipient->users->firstWhere('department_id', $this->department->id);
                        if (!$staff) {
                            Log::warning("No staff found in department ID: {$this->department->id} for recipient ID: {$recipient->id}");
                            $email = config('mail.fallback_email', 'fallback@example.com');
                        } else {
                            $email = $staff->email;
                        }

                        Mail::to($email)->queue(new DocumentWorkflowNotification($firstDraftRecord, $firstStage, "first"));
                        Log::info("Workflow email queued to: {$email} for document ID: {$this->document->id}");

                    } catch (\Exception $e) {
                        Log::error("Error sending email to recipient ID: {$recipient->id} - " . $e->getMessage());
                    }
                }
            });
        } catch (\Exception $e) {
            Log::error("Error handling document workflow for document ID: {$this->document->id} - " . $e->getMessage());
            throw $e; // Optionally rethrow to allow job retry mechanisms
        }
    }
}
