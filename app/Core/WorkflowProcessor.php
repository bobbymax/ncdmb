<?php

namespace App\Core;

use App\Core\Contracts\ServiceResolverInterface;
use App\Core\Contracts\WorkflowProcessorInterface;
use App\Engine\ControlPanel;
use App\Exceptions\Processor\WorkflowContextException;
use App\Models\Document;
use App\Models\DocumentAction;
use App\Models\DocumentDraft;
use App\Models\ProgressTracker;
use Illuminate\Support\Facades\Log;

class WorkflowProcessor implements WorkflowProcessorInterface
{
    private ServiceResolverInterface $serviceResolver;
    private array $validActions = ['first', 'next', 'wait', 'end', 'rollback', 'reject'];

    public function __construct(ServiceResolverInterface $serviceResolver)
    {
        $this->serviceResolver = $serviceResolver;
    }

    public function executeAction(string $action, Document $document, ?DocumentAction $documentAction = null): mixed
    {
        if (!$this->isValidAction($action)) {
            throw new WorkflowContextException(
                "Invalid workflow action: {$action}",
                0,
                null,
                ['action' => $action, 'valid_actions' => $this->validActions]
            );
        }

        try {
            // For 'first' action, workflow might not be assigned yet, so we don't check $document->workflow here.
            // ControlPanel::first() is responsible for setting it up.
            $workflow = $document->workflow;

            if ($action !== 'first' && !$workflow) {
                throw new WorkflowContextException(
                    "Document {$document->id} does not have an associated workflow for action '{$action}'"
                );
            }

            // Pass null for workflow if action is 'first' and it's not yet assigned
            $controlPanelWorkflow = ($action === 'first' && !$workflow) ? null : $workflow;

            $controlPanel = new ControlPanel($this->serviceResolver, $document, $controlPanelWorkflow, $documentAction);
            return $controlPanel->{$action}();
        } catch (\Throwable $e) {
            Log::error('WorkflowProcessor: Failed to execute workflow action', [
                'action' => $action,
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getCurrentTracker(Document $document): ProgressTracker
    {
        if (!$document->progress_tracker_id) {
            throw new WorkflowContextException("Document {$document->id} does not have a current tracker");
        }

        $tracker = ProgressTracker::find($document->progress_tracker_id);

        if (!$tracker) {
            throw new WorkflowContextException("Current tracker {$document->progress_tracker_id} not found");
        }

        return $tracker;
    }

    public function getCurrentDraft(Document $document): ?DocumentDraft
    {
        return $document->drafts()
            ->where('progress_tracker_id', $document->progress_tracker_id)
            ->latest()
            ->first();
    }

    public function isValidAction(string $action): bool
    {
        return in_array($action, $this->validActions);
    }

    public function getAvailableActions(): array
    {
        return $this->validActions;
    }
}