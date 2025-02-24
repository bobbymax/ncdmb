<?php

namespace App\Http\Controllers;

use App\Engine\ControlEngine;
use App\Http\Requests\HandleServiceRequest;
use App\Models\{Document, ProgressTracker, Workflow};
use App\Services\{DocumentActionService, DocumentService, ProgressTrackerService, WorkflowService, DocumentDraftService};
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;

class ServiceWorkerController extends Controller
{
    use ApiResponse;
    protected DocumentService $documentService;
    protected ProgressTrackerService $progressTrackerService;
    protected DocumentActionService $documentActionService;
    protected WorkflowService $workflowService;
    protected ControlEngine $controlEngine;

    public function __construct(
        DocumentService $documentService,
        ProgressTrackerService $progressTrackerService,
        DocumentActionService $documentActionService,
        WorkflowService $workflowService,
        ControlEngine $controlEngine
    )
    {
        $this->documentService = $documentService;
        $this->progressTrackerService = $progressTrackerService;
        $this->documentActionService = $documentActionService;
        $this->workflowService = $workflowService;
        $this->controlEngine = $controlEngine;
    }

    /**
     * @throws ValidationException
     * @throws \Exception
     */
    public function handleService(HandleServiceRequest $request, $service): \Illuminate\Http\JsonResponse
    {
        if (!App::bound($service)) {
            return $this->error(null, 'Invalid service provided', 400);
        }

        // Fetch required models
        $workflow = $this->workflowService->show($request->workflow_id);
        $document = $this->documentService->show($request->document_id);

        if (!$workflow || !$document) {
            return $this->error(null, 'Invalid workflow or document ID', 404);
        }

        $currentTracker = $this->currentTracker($workflow, $document);
        $action = $this->documentActionService->show($request->document_action_id);

        if (!$action) {
            return $this->error(null, 'Invalid tracker or action data', 404);
        }

        // Process workflow
        $this->controlEngine->initialize(
            app($service),
            $document,
            $workflow,
            $currentTracker,
            $action,
            $request->state,
            $request->signature,
            $request->state['message'] ?? null
        );

        $this->controlEngine->process();

        return $this->success(null, class_basename(app($service)) . " {$action->action_status}  processed successfully");
    }

    private function currentTracker(Workflow $workflow, Document $document): ProgressTracker
    {
        if (!empty($document->drafts)) {
            $draft = $document->drafts()->latest()->first();
            return $this->progressTrackerService->show($draft->progress_tracker_id);
        }

        return $workflow->trackers()->where('order', 1)->first();
    }
}
