<?php

namespace App\Engine;

use App\Events\WorkflowStateChange;
use App\Models\{ Document, DocumentAction, DocumentDraft, Workflow, ProgressTracker };
use App\Services\BaseService;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\{Auth, DB, Log};
use Illuminate\Support\Str;

class ControlEngine
{
    protected BaseService $baseService;
    protected Document $document;
    protected Workflow $workflow;
    protected DocumentAction $documentAction;
    protected ProgressTracker $tracker;
    protected ?DocumentDraft $lastDraft;
    protected $user;
    protected ?array $state;
    protected ?string $signature;
    protected ?string $message;

    public function initialize(
        BaseService $baseService,
        Document $document,
        Workflow $workflow,
        ProgressTracker $tracker,
        ?DocumentAction $documentAction = null,
        ?array $state = [],
        ?string $signature = null,
        ?string $message = null
    ): void {
        $this->baseService = $baseService;
        $this->documentAction = $documentAction ?? $this->getDocumentActionForCreation();
        $this->document = $document;
        $this->workflow = $workflow;
        $this->tracker = $tracker;
        $this->state = $state;
        $this->signature = $signature;
        $this->message = $message;
        $this->user = Auth::user();
        $this->lastDraft = $document->drafts()->latest()->first();
    }

    public function process()
    {
        Log::info("Processing workflow for user: {$this->user->id}, Document ID: {$this->document->id}");

        return DB::transaction(fn() => match ($this->documentAction->action_status) {
            'passed' => $this->proceed(),
            'failed', 'cancelled' => $this->terminate(),
            default => $this->stall(),
        }, 3);
    }

    /**
     * @throws Exception
     */
    protected function proceed(): ProgressTracker
    {
        if (!empty($this->state) && isset($this->state['resource_id'], $this->state['data'])) {
            $this->baseService->update($this->state['resource_id'], ...$this->state['data']);
        }

        return $this->lastDraft ? $this->next() : $this->first();
    }

    private function getDocumentActionForCreation()
    {
        return DocumentDraft::where('label', 'create-resource')->first();
    }

    private function first(): ProgressTracker
    {
        Log::info("Creating first draft for Document ID: {$this->document->id}");
        $firstStage = DB::transaction(fn() => $this->createDraft("pending"));
        $this->dispatchWorkflowEvent();

        return $firstStage;
    }

    private function next(): ProgressTracker
    {
        Log::info("Proceeding to next stage for Document ID: {$this->document->id}");
        $nextStage = DB::transaction(function () {
            if ($this->lastDraft?->type === "attention") {
                $this->createDraft("responded", null, 0, "response");
                $this->document->update(['document_action_id' => $this->documentAction->id]);
                return $this->tracker;
            }

            $nextTracker = $this->getTracker($this->tracker->order + 1);
            $this->lastDraft?->update(['status' => $this->documentAction->button_text]);
            $this->addUpdate();

            if (!$nextTracker) {
                $this->document->update(['status' => 'completed']);
                return $this->tracker;
            }

            $this->document->update([
                'progress_tracker_id' => $nextTracker->id,
                'document_action_id' => $this->documentAction->id
            ]);

            $this->createDraft('pending', $nextTracker);

            return $nextTracker;
        });

        $this->dispatchWorkflowEvent($nextStage);
        return $nextStage;
    }

    private function getTracker(int $order): ?ProgressTracker
    {
        return $this->workflow->trackers()->where('order', $order)->first();
    }

    private function fallback(): int
    {
        return optional($this->tracker->stage->fallback)->id ?? 0;
    }

    protected function stall(): ProgressTracker
    {
        Log::info("Stalling workflow for Document ID: {$this->document->id}");
        $stalled = DB::transaction(function () {
            $this->lastDraft?->update(['status' => 'stalled']);

            $this->createDraft(
                "pending",
                null,
                $this->fallback(),
                'attention'
            );

            $this->document->update([
                'document_action_id' => $this->documentAction->id
            ]);

            $this->addUpdate();
            return $this->tracker;
        });

        $this->dispatchWorkflowEvent();
        return $stalled;
    }

    protected function terminate(): ProgressTracker
    {
        Log::info("Terminating workflow for Document ID: {$this->document->id}");

        $tracker = DB::transaction(function () {
            $this->lastDraft?->update(['status' => $this->documentAction->button_text]);
            $this->createDraft('terminated', null, $this->fallback());
            $this->document->update([
                'document_action_id' => $this->documentAction->id
            ]);
            $this->addUpdate();

            return $this->tracker;
        });


        $this->dispatchWorkflowEvent();

        return $tracker;
    }

    private function addUpdate(): void
    {
        if (empty($this->state)) {
            return;
        }

        if (!isset($this->state['document_draft_id']) || !$this->message) {
            return;
        }

        $draft = DocumentDraft::find($this->state['document_draft_id']);

        if (!$draft) {
            Log::warning("Attempted to update a non-existent draft with ID: {$this->state['document_draft_id']}");
            return;
        }

        $draft->updates()->create([
            'user_id' => $this->user->id,
            'document_draft_id' => $draft->id,
            'document_action_id' => $this->documentAction->id,
            'comment' => $this->message,
        ]);
    }

    /**
     * @param string $status
     * @param ProgressTracker|null $tracker
     * @param int $stageId
     * @param string $type
     * @return DocumentDraft
     * @throws Exception
     */
    private function createDraft(string $status, ?ProgressTracker $tracker = null, int $stageId = 0, string $type = "paper"): DocumentDraft
    {
        $tracker = $tracker ?? $this->tracker;
        return $this->document->drafts()->create([
            'document_type_id' => $tracker->document_type_id,
            'carder_id' => $tracker->carder_id,
            'group_id' => $tracker->group_id,
            'created_by_user_id' => $this->user->id,
            'progress_tracker_id' => $tracker->id,
            'current_workflow_stage_id' => $stageId ?? $tracker->workflow_stage_id,
            'department_id' => $tracker->department_id ?: $this->document->department_id,
            'document_draftable_id' => $this->state['resource_id'],
            'document_draftable_type' => get_class($this->resolveServiceToModel()),
            'signature' => $this->signature,
            'is_signed' => (bool) ($this->state['is_signed'] ?? false),
            'status' => $status,
            'type' => $type,
        ]);
    }

    private function resolveServiceToModel(): Model
    {
        $modelName = Str::replaceLast('Service', '', class_basename($this->baseService));
        $modelClass = "App\\Models\\{$modelName}";

        if (!class_exists($modelClass)) {
            throw new Exception("Model class {$modelClass} does not exist.");
        }

        return app($modelClass);
    }

    /**
     * Handles workflow state change event dispatching.
     */
    private function dispatchWorkflowEvent(?ProgressTracker $nextTracker = null): void
    {
        event(new WorkflowStateChange(
            $this->document,
            $this->documentAction,
            $this->user->id,
            $this->tracker,
            $nextTracker
        ));
    }
}
