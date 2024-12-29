<?php

namespace App\Jobs;

use App\Models\Document;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandleDocumentWorkflow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Document $document;
    public int $draftable_id;
    public string $draftable_type;
    /**
     * Create a new job instance.
     */
    public function __construct(Document $document, int $draftable_id, string $draftable_type)
    {
        $this->document = $document;
        $this->draftable_id = $draftable_id;
        $this->draftable_type = $draftable_type;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $workflow = $this->document->documentType->workflow;

        $firstStage = $workflow->workflowStages()->orderBy('order')->first();

        $firstDraft = [
            'document_id' => $this->document->id,
            'group_id' => $firstStage->group_id,
            'created_by_user_id' => $this->document->user_id,
            'current_workflow_stage_id' => $firstStage->id,
            'department_id' => $firstStage->department_id,
            'document_draftable_id' => $this->draftable_id,
            'document_draftable_type' => $this->draftable_type,
            'status' => $firstStage->name
        ];

        $this->document->drafts()->create($firstDraft);
    }
}
