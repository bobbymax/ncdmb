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
            $record =  $this->handleServiceStateUpdate();

            return match ($this->documentAction->action_status) {
                'passed' => $this->proceed($record),
                'failed', 'cancelled' => $this->terminate($record),
                'recall' => $this->recall($record),
                default => $this->stall(),
            };
        }, 3);
    }

    /**
     * @throws Exception
     */
    protected function handleServiceStateUpdate()
    {
        if (!empty($this->state) && isset($this->state['resource_id'], $this->state['data'], $this->state['mode']) && !empty($this->state['data'])) {
            return $this->state['mode'] === "store"
                ? $this->baseService->store($this->state['data'])
                : $this->baseService->update($this->state['resource_id'], $this->state['data']);
        }

        return null;
    }

    /**
     * @throws Exception
     */
    protected function proceed($record = null): ProgressTracker
    {
        return $this->lastDraft ? $this->next($record) : $this->first($record);
    }

    /**
     * @throws Exception
     */
    private function handleResourceServiceDeletion(string $document_type, string $draft_type, int $draftable_id): bool
    {
        if ($document_type === "" || $draft_type === "") return false;
        if ($document_type === $draft_type) return false;

        try {
            $service = $this->resolveModelToService($draft_type);
            $service->destroy($draftable_id);
        } catch (\Exception $e) {
            Log::error('Error Resolving Model Class to Service ' . $e->getMessage());
        }

        return true;
    }

    private function recall($record = null)
    {
        return DB::transaction(function () use ($record) {
            if (!$this->lastDraft) {
                return $this->tracker;
            }

            $previousTracker = $this->getPreviousTracker();
            $firstDraft = $this->document->drafts()->oldest()->first();

            // Compare the lastDraft document_draftable_type to documentable_type
            // If not the same, Delete the type from last draft
            $this->handleResourceServiceDeletion(
                $this->document->documentable_type,
                $this->lastDraft->document_draftable_type,
                $this->lastDraft->document_draftable_id
            );

            if ($firstDraft && $this->lastDraft->id !== $firstDraft?->id) {
                // Update the Document History here
                $this->lastDraft->delete();
                $this->lastDraft = $this->document->drafts()->latest()->first();
            }

            $this->lastDraft?->update([
                'status' => 'pending',
                'document_action_id' => $this->documentAction->id,
                'authorising_staff_id' => 0,
            ]);

            $this->document->update([
                'progress_tracker_id' => $previousTracker?->id ?? $this->tracker->id,
                'status' => 'recalled'
            ]);

            $this->dispatchWorkflowEvent($previousTracker ?? $this->tracker);
            return $previousTracker ?? $this->tracker;
        });
    }

    /**
     * @throws Exception
     */
    private function first($record = null): ProgressTracker
    {
        Log::info("Creating first draft for Document ID: {$this->document->id}");
        $this->createDraft("pending", $record);
        $this->dispatchWorkflowEvent();

        return $this->tracker;
    }

    private function next($record = null): ProgressTracker
    {
        Log::info("Proceeding to next stage for Document ID: {$this->document->id}");
        $nextStage = DB::transaction(function () use ($record) {
            if ($this->lastDraft?->type === "attention") {
                $this->updateLastDraft();
                $this->document->update(['document_action_id' => $this->documentAction->id]);
                return $this->tracker;
            }

            $this->updateLastDraft();

            if (!$nextTracker = $this->getNextTracker()) {
                $this->document->update(['status' => 'completed']);
                return $this->tracker;
            }

            $this->document->update([
                'progress_tracker_id' => $nextTracker->id,
                'document_action_id' => $this->documentAction->id
            ]);

            $this->createDraft('pending', $record, $nextTracker);
            return $nextTracker;
        });

        $this->dispatchWorkflowEvent($nextStage);
        return $nextStage;
    }

    private function getPreviousTracker(): ?ProgressTracker
    {
        return $this->workflow->trackers()->where('order', '<', $this->tracker->order)->latest('order')->first();
    }

    private function getNextTracker(): ?ProgressTracker
    {
        return $this->workflow->trackers()->where('order', $this->tracker->order + 1)->first();
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
        $this->updateLastDraft("attention");
        $this->document->update(['document_action_id' => $this->documentAction->id]);

        $this->dispatchWorkflowEvent();
        return $this->tracker;
    }

    /**
     * @throws Exception
     */
    protected function terminate($record = null): ProgressTracker
    {
        Log::info("Terminating workflow", ['document_id' => $this->document->id]);
        $this->updateLastDraft();
        $this->document->update([
            'document_action_id' => $this->documentAction->id,
            'status' => 'terminated',
        ]);

        $this->dispatchWorkflowEvent();
        return $this->tracker;
    }

    private function updateLastDraft(string $type = "response"): void
    {
        if ($this->lastDraft?->type === "attention") {
            $this->lastDraft->update([
                'status' => 'responded',
                'type' => $type,
                'authorising_staff_id' => $this->user->id,
                'document_action_id' => $this->documentAction->id,
                'current_workflow_stage_id' => $this->tracker->stage->id
            ]);
        } else {
            $this->lastDraft?->update([
                'document_action_id' => $this->documentAction->id,
                'current_workflow_stage_id' => $this->fallback() > 0 ? $this->fallback() : $this->tracker->workflow_stage_id,
                'signature' => $this->signatureUpload($this->signature ?? ""),
                'status' => $this->documentAction->draft_status,
                'authorising_staff_id' => $this->user->id,
            ]);
        }
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
        $record = null,
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
            'document_draftable_id' => $record ? $record?->id : $this->state['resource_id'],
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
        $modelClass = "App\\Models\\" . Str::replaceLast('Service', '', class_basename($this->baseService));
        return class_exists($modelClass) ? app($modelClass) : app(DocumentUpdate::class);
    }

    /**
     * @throws Exception
     */
    private function resolveModelToService(string $modelClass)
    {
        // Ensure the model class exists
        if (!class_exists($modelClass)) {
            throw new \Exception("Model class {$modelClass} does not exist.");
        }

        // Convert Model class name to Service class name
        $serviceClass = 'App\\Services\\' . class_basename($modelClass) . 'Service';

        // Check if the service class exists
        if (!class_exists($serviceClass)) {
            throw new \Exception("Service class {$serviceClass} does not exist.");
        }

        // Resolve the service class via Laravel container
        return app($serviceClass);
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
