<?php

namespace App\Core;

use App\Models\{Document, DocumentAction, DocumentDraft, ProgressTracker, User, Workflow, WorkflowStage};
use App\Services\BaseService;
use Illuminate\Support\Facades\{Auth, DB, Log};
use Illuminate\Support\Str;
use Exception;

class WorkflowProcessor
{
    protected BaseService $baseService;
    protected Document $document;
    protected Workflow $workflow;
    protected DocumentAction $action;
    protected ?DocumentDraft $lastDraft;
    protected ProgressTracker $currentProgressTracker;
    protected $user;
    protected array $state;
    protected ?string $signature;
    protected ?string $message;

    public function __construct(
        BaseService $baseService,
        ProgressTracker $currentProgressTracker,
        Document $document,
        Workflow $workflow,
        DocumentAction $action,
        array $state,
        ?string $signature = null,
        ?string $message = null
    ) {
        $this->baseService = $baseService;
        $this->document = $document;
        $this->workflow = $workflow;
        $this->action = $action;
        $this->user = auth()->user();
        $this->state = $state;
        $this->signature = $signature;
        $this->message = $message;

        $this->lastDraft = $document->drafts()->latest()->first();
        $this->currentProgressTracker = $currentProgressTracker;
    }

    public function process()
    {
        Log::info("Processing workflow for user: {$this->user->id}, Document ID: {$this->document->id}");

        return DB::transaction(function () {
            return match ($this->action->action_status) {
                'passed' => $this->gotoNextStage(),
                'failed', 'cancelled', 'attend' => $this->terminateProcess(),
                default => $this->stayOnTracker(),
            };
        }, 3);
    }

    private function stayOnTracker(): ProgressTracker
    {
        if ($this->lastDraft) {
            $this->lastDraft->update(['status' => $this->action->action_status]);
        }

        return $this->currentProgressTracker;
    }

    /**
     * @throws Exception
     */
    private function gotoNextStage()
    {
        if ($this->lastDraft) {
            $this->lastDraft->update(['status' => $this->action->action_status]);
        }

        $currentStage = $this->getWorkflowStage($this->lastDraft?->current_workflow_stage_id);
        if (!$currentStage) {
            throw new Exception("Current workflow stage not found for document ID: {$this->document->id}");
        }

        if (
            $this->currentProgressTracker->fallback_to_stage_id === $currentStage->id ||
            $this->currentProgressTracker->return_to_stage_id === $currentStage->id
        ) {
            return $this->handleQueryAndResponse();
        }

        return $this->proceedToNextStage();
    }

    /**
     * @throws Exception
     */
    private function handleQueryAndResponse()
    {
        $returnStageId = $this->currentProgressTracker->return_to_stage_id;
        if ($returnStageId) {
            $returnToStage = $this->getWorkflowStage($returnStageId);
            if (!$returnToStage) {
                throw new Exception("Return stage not found for tracker ID: {$this->currentProgressTracker->id}");
            }

            $this->createNextDraft(
                $this->currentProgressTracker,
                $returnToStage,
                "response"
            );
            return $this->currentProgressTracker;
        }

        return $this->proceedToNextStage();
    }

    /**
     * @throws Exception
     */
    private function terminateProcess(): ?ProgressTracker
    {
        if ($this->lastDraft) {
            $this->lastDraft->update(['status' => 'terminated']);
        }

        $fallbackStage = $this->getWorkflowStage($this->currentProgressTracker->fallback_to_stage_id) ?? $this->currentProgressTracker->stage;
        if ($fallbackStage) {
            $this->createNextDraft($this->currentProgressTracker, $fallbackStage, "terminated");
        }

        return $this->currentProgressTracker;
    }

    /**
     * @throws Exception
     */
    private function proceedToNextStage()
    {
        $nextTracker = $this->workflow->trackers()->where('order', $this->currentProgressTracker->order + 1)->first();
        if (!$nextTracker) {
            Log::info("Workflow completed for document ID: {$this->document->id}");
            $this->document->update(['status' => 'completed']);
            return null;
        }

        $draftableId = $this->state['resource_id'] ?? throw new Exception("Missing resource_id in state.");

        return DB::transaction(function () use ($nextTracker, $draftableId) {
            $this->document->update(['progress_tracker_id' => $nextTracker->id]);

            if (!empty($this->state)) {
                $this->baseService->update($draftableId, $this->state, false);
            }

            $this->createNextDraft(
                $nextTracker,
                $nextTracker->stage,
                "pending"
            );

            return $nextTracker;
        }, 3);
    }

    /**
     * @throws Exception
     */
    private function createNextDraft(
        ProgressTracker $tracker,
        WorkflowStage $stage,
        string $status,
    ): void {
        DB::transaction(function () use ($tracker, $stage, $status) {
            try {
                $this->document->drafts()->create([
                    'document_type_id' => $tracker->document_type_id,
                    'group_id' => $stage->group_id,
                    'created_by_user_id' => $this->user->id,
                    'progress_tracker_id' => $tracker->id,
                    'current_workflow_stage_id' => $stage->id,
                    'department_id' => $stage->department_id ?: $this->document->department_id,
                    'document_draftable_id' => $this->state['resource_id'],
                    'document_draftable_type' => get_class($this->resolveServiceToModel()),
                    'signature' => $this->signature,
                    'status' => $status,
                ]);
            } catch (Exception $e) {
                Log::error("Failed to create draft for document ID: {$this->document->id}. Error: {$e->getMessage()}");
                throw $e;
            }
        }, 3);
    }

    private function resolveServiceToModel(): object
    {
        $modelName = Str::replaceLast('Service', '', class_basename($this->baseService));
        $modelClass = "App\\Models\\{$modelName}";

        if (!class_exists($modelClass)) {
            throw new Exception("Model class {$modelClass} does not exist.");
        }

        return app($modelClass);
    }

    private function getWorkflowStage($stageId): ?WorkflowStage
    {
        if (!$stageId) {
            return null;
        }

        $stage = WorkflowStage::find($stageId);
        if (!$stage) {
            Log::error("Workflow stage not found for ID: {$stageId}");
        }

        return $stage;
    }
}
