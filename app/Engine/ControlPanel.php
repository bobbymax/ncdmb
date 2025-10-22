<?php

namespace App\Engine;

use App\Core\Contracts\ServiceResolverInterface;
use App\Models\Document;
use App\Models\DocumentAction;
use App\Models\DocumentDraft;
use App\Models\ProgressTracker;
use App\Models\Workflow;
use Illuminate\Support\Facades\Auth;

class ControlPanel
{
    protected ServiceResolverInterface $serviceResolver;
    protected Document $document;
    protected Workflow $workflow;
    protected ?DocumentAction $documentAction;

    public function __construct(
        ServiceResolverInterface $serviceResolver,
        Document $document,
        Workflow $workflow,
        ?DocumentAction $documentAction = null
    ) {
        $this->serviceResolver = $serviceResolver;
        $this->document = $document;
        $this->workflow = $workflow;
        $this->documentAction = $documentAction;
    }

    /**
     * Create the first draft of a document
     * Gets the first tracker (order == 1) and creates initial draft
     */
    public function first(): DocumentDraft
    {
        // Get the first tracker using order == 1
        $firstTracker = $this->workflow->trackers()
            ->where('order', 1)
            ->first();

        if (!$firstTracker) {
            throw new \RuntimeException('No first tracker found for workflow ID: ' . $this->workflow->id);
        }

        // Create a draft of the document
        return $this->createADraftOfTheDocument($firstTracker);
    }

    /**
     * Move to the next tracker in the workflow
     * Creates a new draft for the next stage
     */
    public function next(): DocumentDraft
    {
        $currentTracker = $this->getCurrentTracker();

        // Get the next tracker in sequence
        $nextTracker = $this->workflow->trackers()
            ->where('order', '>', $currentTracker->order)
            ->orderBy('order')
            ->first();

        if (!$nextTracker) {
            throw new \RuntimeException('No next tracker found for workflow ID: ' . $this->workflow->id);
        }

        // Update the last draft with operator_id
        $lastDraft = $this->getCurrentDraft();
        $lastDraft?->update(['status' => $this->documentAction->draft_status]);

        // Create new draft for next stage
        $draft = $this->createADraftOfTheDocument($nextTracker);

        $this->document->update([
            'status' => $this->documentAction?->draft_status ?? 'pending',
            'progress_tracker_id' => $nextTracker->id,
        ]);

        return $draft;
    }

    /**
     * Wait at current tracker (pause workflow)
     * Updates current draft status to waiting
     */
    public function wait(): DocumentDraft
    {
        $currentDraft = $this->getCurrentDraft();

        if (!$currentDraft) {
            throw new \RuntimeException('No current draft found for document ID: ' . $this->document->id);
        }

        // Update draft status to waiting
        $currentDraft->update([
            'status' => $this->documentAction?->draft_status ?? 'stalled',
        ]);

        // Update document status
        $this->document->update([
            'status' => $this->documentAction?->draft_status ?? 'stalled',
            'progress_tracker_id' => $currentDraft->progress_tracker_id,
        ]);

        return $currentDraft->fresh();
    }

    /**
     * End the workflow (complete the process)
     * Marks the document and current draft as completed
     */
    public function end(): DocumentDraft
    {
        $currentDraft = $this->getCurrentDraft();

        if (!$currentDraft) {
            throw new \RuntimeException('No current draft found for document ID: ' . $this->document->id);
        }

        // Update draft status to completed
        $currentDraft->update([
            'status' => $this->documentAction?->draft_status ?? 'completed',
        ]);

        // Update document status to approved/completed
        $this->document->update([
            'status' => $this->documentAction?->draft_status ?? 'approved',
            'progress_tracker_id' => $currentDraft->progress_tracker_id,
        ]);

        return $currentDraft->fresh();
    }

    /**
     * Rollback to previous tracker
     * Creates a new draft for the previous stage
     */
    public function rollback(): DocumentDraft
    {
        $currentTracker = $this->getCurrentTracker();

        // Get the previous tracker in sequence
        $previousTracker = $this->workflow->trackers()
            ->where('order', '<', $currentTracker->order)
            ->orderByDesc('order')
            ->first();

        if (!$previousTracker) {
            throw new \RuntimeException('No previous tracker found for workflow ID: ' . $this->workflow->id);
        }

        // Create new draft for previous stage
        $draft = $this->createADraftOfTheDocument($previousTracker);

        // Update the last draft with operator_id
        $lastDraft = $this->getCurrentDraft();
        if ($lastDraft) {
            $lastDraft->update(['status' => $this->documentAction?->draft_status ?? 'reversed']);
            $this->document->update([
                'status' => $this->documentAction?->draft_status ?? 'reversed',
                'progress_tracker_id' => $previousTracker->id,
            ]);
        }

        return $draft;
    }

    /**
     * Reject the document (workflow failure)
     * Marks the document and current draft as rejected
     */
    public function reject(): DocumentDraft
    {
        $currentDraft = $this->getCurrentDraft();

        if (!$currentDraft) {
            throw new \RuntimeException('No current draft found for document ID: ' . $this->document->id);
        }

        // Update draft status to rejected
        $currentDraft->update([
            'status' => $this->documentAction?->draft_status ?? 'rejected',
            'operator_id' => Auth::id(),
        ]);

        // Update document status to rejected
        $this->document->update([
            'status' => $this->documentAction?->draft_status ?? 'rejected',
            'progress_tracker_id' => $currentDraft->progress_tracker_id,
        ]);

        return $currentDraft->fresh();
    }

    /**
     * Get the current tracker for the document
     */
    protected function getCurrentTracker(): ProgressTracker
    {
        $tracker = ProgressTracker::find($this->document->progress_tracker_id);

        if (!$tracker) {
            throw new \RuntimeException('No current tracker found for document ID: ' . $this->document->id);
        }

        return $tracker;
    }

    /**
     * Get the current draft for the document
     */
    protected function getCurrentDraft(): ?DocumentDraft
    {
        return $this->document->drafts()
            ->where('progress_tracker_id', $this->document->progress_tracker_id)
            ->latest()
            ->first();
    }

    /**
     * @param $firstTracker
     * @return mixed
     */
    public function createADraftOfTheDocument($firstTracker): mixed
    {
        $departmentId = $firstTracker->department_id < 1 ? Auth::user()->department_id : $firstTracker->department_id;

        $draft = DocumentDraft::create([
            'document_id' => $this->document->id,
            'progress_tracker_id' => $firstTracker->id,
            'group_id' => $firstTracker->group_id,
            'department_id' => $departmentId,
            'current_workflow_stage_id' => $firstTracker->workflow_stage_id,
            'carder_id' => $firstTracker->carder_id,
            'document_action_id' => $this->documentAction?->id ?? 0,
            'permission' => $firstTracker->permission,
            'status' => 'pending',
        ]);

        // Update the document with current tracker reference
        $this->document->update([
            'progress_tracker_id' => $firstTracker->id,
            'status' => $this->documentAction?->draft_status ?? 'pending',
        ]);
        return $draft;
    }
}
