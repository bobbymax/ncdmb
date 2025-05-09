<?php

namespace App\Http\Controllers;

use App\Core\NotificationProcessor;
use App\Engine\ControlEngine;
use App\Http\Requests\HandleServiceRequest;
use App\Http\Resources\DocumentResource;
use App\Models\{Document, ProgressTracker, Workflow};
use App\Services\{DocumentActionService, DocumentService, ProgressTrackerService, WorkflowService, DocumentDraftService};
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
    ) {
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
            $request->serverState,
            $request->signature,
            $request->serverState['message'] ?? null,
            $request->amount ?? null
        );

        $this->controlEngine->process();

        NotificationProcessor::for(
            $document->id,
            Auth::id(),
            $action->id
        )->sendAll();

        return $this->success(new DocumentResource($document), class_basename(app($service)) . " {$action->action_status}  processed successfully");
    }

    public function handleProcessServiceData(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'document_id' => 'required|integer|exists:documents,id',
            'document_action_id' => 'required|integer|exists:document_actions,id',
        ]);

        if ($validator->fails()) {
            return $this->error(null, $validator->errors(), 400);
        }

        DB::beginTransaction();

        try {
            $document = processor()->resourceResolver($request->document_id, 'document');
            $task = processor()->handleFrontendRequest($request);
            $task->execute();

            DB::commit();

            // 🟢 Outside transaction: Send notifications
            NotificationProcessor::for(
                $request->document_id,
                Auth::id(),
                $request->document_action_id
            )->sendAll();

            return $this->success(new DocumentResource($document));

        } catch (\InvalidArgumentException $e) {
            DB::rollBack();
            return $this->error(null, $e->getMessage(), $e->getCode() ?: 400);
        } catch (ValidationException $e) {
            DB::rollBack();
            return $this->error(null, $e->validator->errors()->first(), 400);
        } catch (\BadMethodCallException $e) {
            DB::rollBack();
            return $this->error(null, $e->getMessage(), 400);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error(null, $e->getMessage(), 500);
        }
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
