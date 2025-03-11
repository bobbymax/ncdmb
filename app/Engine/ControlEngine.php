<?php

namespace App\Engine;

use App\Events\WorkflowStateChange;
use App\Traits\DocumentFlow;
use App\Models\{Document, DocumentAction, DocumentDraft, DocumentUpdate, Workflow, ProgressTracker};
use App\Services\BaseService;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\{Auth, DB, Log};
use Illuminate\Support\Str;

class ControlEngine
{

    use DocumentFlow;

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
    protected ?float $amount;

    public function initialize(
        BaseService $baseService,
        Document $document,
        Workflow $workflow,
        ProgressTracker $tracker,
        DocumentAction $documentAction,
        ?array $state = [],
        ?string $signature = null,
        ?string $message = null,
        ?float $amount = null
    ): void {
        $this->baseService = $baseService;
        $this->documentAction = $documentAction;
        $this->document = $document;
        $this->workflow = $workflow;
        $this->tracker = $tracker;
        $this->state = $state;
        $this->signature = $signature ?? "";
        $this->message = $message;
        $this->user = Auth::user();
        $this->lastDraft = $document->drafts()->latest()->first();
        $this->amount = $amount;
    }

    public function process()
    {
        Log::info("Processing workflow for user: {$this->user->id}, Document ID: {$this->document->id}");

        return DB::transaction(function () {
            $documentStateUpdate = match ($this->documentAction->action_status) {
                'passed' => $this->proceed(),
                'failed', 'cancelled' => $this->terminate(),
                default => $this->stall(),
            };

            if ($documentStateUpdate) {
                $this->handleServiceStateUpdate();
            }

            return $documentStateUpdate;
        }, 3);

//        return DB::transaction(fn() => match ($this->documentAction->action_status) {
//            'passed' => $this->proceed(),
//            'failed', 'cancelled' => $this->terminate(),
//            default => $this->stall(),
//        }, 3);
    }

    protected function handleServiceStateUpdate(): void
    {
        if (!empty($this->state) && isset($this->state['resource_id'], $this->state['data'], $this->state['mode']) && !empty($this->state['data'])) {
            Log::info('The Mode Here is ' . $this->state['mode']);
            $this->state['mode'] === "store"
                ? $this->baseService->store($this->state['data'])
                : $this->baseService->update($this->state['resource_id'], $this->state['data']);
        }
    }

    /**
     * @throws Exception
     */
    protected function proceed(): ProgressTracker
    {
//        $this->handleServiceStateUpdate();
        return $this->lastDraft ? $this->next() : $this->first();
    }

    /**
     * @throws Exception
     */
    private function first(): ProgressTracker
    {
        Log::info("Creating first draft for Document ID: {$this->document->id}");
        $this->createDraft("pending");
        $this->dispatchWorkflowEvent();

        return $this->tracker;
    }

    private function next(): ProgressTracker
    {
        Log::info("Proceeding to next stage for Document ID: {$this->document->id}");
        $nextStage = DB::transaction(function () {
            if ($this->lastDraft?->type === "attention") {
                $this->lastDraft->update([
                    'current_workflow_stage_id' => $this->tracker->stage->id,
                    'status' => 'responded',
                    'type' => 'response'
                ]);

                $this->document->update(['document_action_id' => $this->documentAction->id]);
                return $this->tracker;
            }

            $authorisingOfficer = ($this->lastDraft && $this->lastDraft->workflowStage->append_signature) ? $this->user->id : 0;

            $this->lastDraft?->update([
                'signature' => $this->signatureUpload($this->signature),
                'status' => $this->documentAction->draft_status,
                'authorising_staff_id' => $authorisingOfficer
            ]);

            if (!$nextTracker = $this->getTracker($this->tracker->order + 1)) {
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
        $this->lastDraft?->update([
            'current_workflow_stage_id' => $this->fallback() < 1 ? $this->tracker->workflow_stage_id : $this->fallback(),
            'status' => 'stalled',
            'type' => 'attention'
        ]);

        $this->document->update(['document_action_id' => $this->documentAction->id]);
//        $this->handleServiceStateUpdate();

        $this->dispatchWorkflowEvent();
        return $this->tracker;
    }

    /**
     * @throws Exception
     */
    protected function terminate(): ProgressTracker
    {
        Log::info("Terminating workflow", ['document_id' => $this->document->id]);

        $this->lastDraft?->update(['status' => $this->documentAction->draft_status]);
        $this->createDraft('terminated', null, $this->fallback());
        $this->document->update(['document_action_id' => $this->documentAction->id]);
//        $this->handleServiceStateUpdate();

        $this->dispatchWorkflowEvent();
        return $this->tracker;
    }

    /**
     * @param string $status
     * @param ProgressTracker|null $tracker
     * @param int $stageId
     * @param string $type
     * @return void
     * @throws Exception
     */
    private function createDraft(
        string $status,
        ?ProgressTracker $tracker = null,
        int $stageId = 0,
        string $type = "paper"
    ): void {
        $tracker = $tracker ?? $this->tracker;
        $this->document->drafts()->create([
            'document_type_id' => $tracker->document_type_id,
            'carder_id' => $tracker->carder_id,
            'group_id' => $tracker->group_id,
            'created_by_user_id' => $this->user->id,
            'progress_tracker_id' => $tracker->id,
            'current_workflow_stage_id' => ($stageId > 0) ? $stageId : $tracker->workflow_stage_id,
            'department_id' => $tracker->department_id ?: $this->document->department_id,
            'document_draftable_id' => $this->state['resource_id'],
            'document_draftable_type' => get_class($this->resolveServiceToModel()),
            'resource_type' => $this->documentAction->resource_type,
            'is_signed' => (bool)($this->state['is_signed'] ?? false),
            'amount' => $this->amount ?? ($this->lastDraft?->amount ?? 0),
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

        return app($modelClass) ?? app(DocumentUpdate::class);
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
