<?php

namespace App\Http\Controllers;


use App\Core\WorkflowProcessor;
use App\Http\Requests\HandleServiceRequest;
use App\Jobs\SendWorkflowNotifications;
use App\Services\{DocumentActionService, DocumentService, ProgressTrackerService, WorkflowService, DocumentDraftService};
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ServiceWorkerController extends Controller
{
    use ApiResponse;
    protected DocumentService $documentService;
    protected ProgressTrackerService $progressTrackerService;
    protected DocumentActionService $documentActionService;
    protected WorkflowService $workflowService;

    public function __construct(
        DocumentService $documentService,
        ProgressTrackerService $progressTrackerService,
        DocumentActionService $documentActionService,
        WorkflowService $workflowService
    )
    {
        $this->documentService = $documentService;
        $this->progressTrackerService = $progressTrackerService;
        $this->documentActionService = $documentActionService;
        $this->workflowService = $workflowService;
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

        $lastDraft = $document->drafts()->latest()->first();
        if (!$lastDraft) {
            return $this->error(null, 'No drafts found for the document', 404);
        }

        $currentTracker = $this->progressTrackerService->show($lastDraft->progress_tracker_id);
        $action = $this->documentActionService->show($request->document_action_id);
        if (!$currentTracker || !$action) {
            return $this->error(null, 'Invalid tracker or action data', 404);
        }

        // Resolve service and model
        $resolvedService = app($service);
        $modelClass = $this->resolveServiceToModel($service);

        if (!$modelClass) {
            return $this->error(null, 'Unable to resolve model for the service', 400);
        }

        // Update resource
        // $resourceId = $request->state['resource_id'];
        // $resolvedService->update($resourceId, $request->state);

        // Process workflow
        $workflowProcessor = new WorkflowProcessor(
            $resolvedService,
            $currentTracker,
            $document,
            $workflow,
            $action,
            $request->state,
            $request->signature
        );

        $newTracker = $workflowProcessor->process();

        if ($newTracker) {
            // TODO: Implement notifications
            // Dispatch async processing (consider using queue)
            // HandleCorProcess::dispatch($newTracker);
            // HandlePreviousProcessNotifications::dispatch($currentTracker)

            SendWorkflowNotifications::dispatch(
                $document,
                $action,
                $currentTracker,
                $newTracker,
                Auth::id()
            );
        }

        return $this->success(null, class_basename($resolvedService) . " {$action->action_status} {$newTracker->id} processed successfully");
    }

    private function resolveServiceToModel(string $serviceClass)
    {
        $resolvedService = app($serviceClass);

        // Get the base name (e.g., "ClaimService" -> "Claim")
        $modelName = Str::replaceLast('Service', '', class_basename($resolvedService));

        // Generate full model class path dynamically
        $modelClass = "App\\Models\\{$modelName}";

        if (!class_exists($modelClass)) {
            throw new \Exception("Model class {$modelClass} does not exist.");
        }

        // Instantiate and return the model
        return new $modelClass();
    }
}
