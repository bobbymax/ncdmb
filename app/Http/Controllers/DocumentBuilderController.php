<?php

namespace App\Http\Controllers;

use App\DTO\DocumentActivityContext;
use App\DTOs\NotificationContext;
use App\Engine\ControlPanel;
use App\Http\Resources\DocumentResource;
use App\Jobs\TriggerDocumentActivityEvent;
use App\Models\Document;
use App\Models\DocumentAction;
use App\Models\DocumentDraft;
use App\Models\ProgressTracker;
use App\Repositories\DocumentActionRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\ThreadRepository;
use App\Repositories\UploadRepository;
use App\Repositories\WorkflowRepository;
use App\Services\NotificationService;
use App\Services\WorkflowService;
use App\Traits\ApiResponse;
use App\Traits\TrackerResolver;
use App\Core\Contracts\ServiceResolverInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Handlers\ValidationErrors;

class DocumentBuilderController extends Controller
{
    use ApiResponse, TrackerResolver;

    public function __construct(
        protected DocumentRepository $documentRepository,
        protected UploadRepository $uploadRepository,
        protected NotificationService $notificationService,
        protected WorkflowRepository $workflowRepository,
        protected DocumentActionRepository $documentActionRepository,
        protected ThreadRepository $threadRepository,
    ) {}

    protected function documentValidations(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'service' => 'required|string',
            'meta_data.policy.scope' => 'nullable|string|in:public,private,confidential,restricted',
            'meta_data.policy.frequency' => 'nullable|string|in:days,weeks,months,years',
            'actions' => 'array',
            'trackers' => 'array',
            'document_owner' => 'nullable',
            'department_owner' => 'nullable',
            'fund' => 'nullable',
            'uploads' => 'array',
            'content' => 'array',
            'approval_memo' => 'nullable',
            'config' => 'required',
            'loggedInUser' => 'required|array',
            'preferences' => 'nullable|array',
            'conversations' => 'nullable|array',

            // watchers normalization
            'watchers' => 'array',
            'watchers.users' => 'array',
            'watchers.users.*.id' => 'required|integer',
            'watchers.users.*.name' => 'nullable|string',
            'watchers.users.*.email' => 'nullable|email',
            'watchers.groups' => 'array',
            'watchers.groups.*.id' => 'required|integer',
            'watchers.groups.*.name' => 'nullable|string',

            'title' => 'nullable|string',
            'category.document_type_id' => 'required|integer|exists:document_types,id',
            'category.type' => 'required|string|in:staff,third-party',
            'mode' => 'required|string|in:store,update',
            'requirements' => 'array',
            'linked_documents' => 'array',
            'performed' => 'nullable|string',
            'created_by' => 'nullable|integer',
        ]);
    }

    public function buildDocument(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validator = $this->documentValidations($request);

            if ($validator->fails()) {
                $error = new ValidationErrors($validator->errors());
                return $this->error($error->getValidationErrors(), $error->getMessage(), 422);
            }

            // Extract inputs (strongly typed & defaulted)
            $userId         = (int) $request->get('user_id');
            $service        = (string) $request->input('service');
            $metaData       = (array)  $request->input('meta_data', []);
            $actions        = (array)  $request->input('actions', []);
            $trackers       = (array)  $request->input('trackers', []);
            $owner          = $request->input('document_owner') ?? null;      // expect ['value'=>id,'label'=>name,'email'=>?]
            $department     = $request->input('department_owner') ?? null;    // expect ['value'=>id, 'label'=>name]
            $fund           = $request->input('fund');
            $uploads        = (array)  $request->input('uploads', []);
            $content        = (array)  $request->input('content', []);
            $approvalMemo   = $request->input('approval_memo');
            $config         = (array)  $request->input('config', []);
            $loggedInUser   = (array)  $request->input('loggedInUser', []);
            $preferences    = (array)  $request->input('preferences', []);
            $watchers       = (array)  $request->input('watchers', []);
            $title          = (string) $request->input('title');
            $category       = (array)  $request->input('category', []);
            $typeId         = (int)    ($category['document_type_id'] ?? 0);
            $mode           = (string) $request->input('mode', 'store');   // fixed: was incorrectly from $category
            $requirements   = (array)  $request->input('requirements', []);
            $actionPerformed= (string) $request->input('performed', 'generated');
            $existingResourceId = (int) $request->input('existing_resource_id', 0);
            $existingDocumentId = (int) $request->input('existing_document_id', 0);
            $conversations      = (array) $request->input('conversations', []);
            $budgetYear        = (int) $request->input('budget_year', 0);
            $type             = (string) $category['type'] ?? 'staff';

            // Guard: we need at least one tracker with order=1 to derive pointer
            $currentPointer = collect($trackers)
                ->map(fn ($x) => is_array($x) ? (object) $x : $x)
                ->firstWhere('order', 1);

            if (!$currentPointer) {
                return $this->error(null, "Missing initial tracker (order = 1).", 422);
            }

            // Defensive: ensure needed pointer fields exist
            $workflowStageId   = (int)    ($currentPointer->workflow_stage_id ?? 0);
            $workflowStageName = (string) ($currentPointer->workflow_stage_name ?? ($currentPointer->label ?? 'Unknown Stage'));
            $pointerIdentifier = (string) ($currentPointer->identifier ?? '');

            if (!$workflowStageId || !$pointerIdentifier) {
                return $this->error(null, "Tracker is missing workflow_stage_id or identifier.", 422);
            }

            $document = null;

            DB::transaction(function () use (
                $service, $content, $mode, $owner, $department, $fund, $config, $title, $typeId,
                $approvalMemo, $metaData, $requirements, $watchers, $uploads, $currentPointer, $category, $existingResourceId,
                $preferences, $userId, $existingDocumentId, $conversations, $budgetYear, $type, &$document
            ) {
                // Resolve Service & persist resource (keep args tiny; your processor() may already do this)
                $resource = processor()->saveResource([
                    'user_id' => $owner ? $owner['value'] : Auth::id(),
                    'category' => $category,
                    'service' => $service,
                    'content' => $content,
                    'department_owner' => $department,
                    'existing_resource_id' => $existingResourceId,
                    'budget_year' => $budgetYear,
                    'existing_document_id' => $existingDocumentId,
                    'fund' => $fund,
                    'type' => $type,
                ], $mode === "update");

                if (!$resource) {
                    throw new \RuntimeException("Resource not found or stored.");
                }

                $departmentId = $department ? $department['value'] : auth()->user()->department_id;

                // Build Document payload
                $documentData = [
                    'user_id'              => $userId,
                    'department_id'        => $departmentId,
                    'document_type_id'     => $typeId ?: null,
                    'document_category_id' => $category['id'] ?? null,
                    'document_reference_id'=> $approvalMemo['value'] ?? 0,
                    'fund_id'              => $fund['value'] ?? null,
                    'config'               => $config,
                    'contents'             => $content,
                    'documentable_id'      => $resource['id'],
                    'documentable_type'    => get_class($resource),
                    'title'                => $title ?: ($resource['title'] ?? 'Title Bar'),
                    'status'               => 'pending',
                    'meta_data'            => $metaData,
                    'uploaded_requirements'=> $requirements,
                    'preferences'          => $preferences ?? [], // keep preferences separate if needed
                    'watchers'             => $watchers,                       // raw watchers snapshot for audit
                    'pointer'              => $currentPointer->identifier,
                    'budget_year'          => $budgetYear,
                ];

                if ($mode === "update" && $existingDocumentId > 0) {
                    $document = $this->documentRepository->update($existingDocumentId, $documentData);
                } else {
                    $documentData['ref'] = $this->documentRepository->generateRef($departmentId, $resource['code']);
                    $documentData['created_by'] = Auth::id();
                    $document = $this->documentRepository->create($documentData);

                    if (!empty($conversations)) {
                        foreach ($conversations as $valueDump) {
                            unset($valueDump['id']);
                            unset($valueDump['created_at']);
                            unset($valueDump['conversations']);

                            if ((int) $valueDump['thread_owner_id'] == Auth::id()) continue;

                            $conversation = $this->threadRepository->create([
                                ...$valueDump,
                                'document_id' => $document->id,
                                'recipient_id' => Auth::id(),
                            ]);

                            if (!$conversation) continue;
                        }
                    }
                }

                if (!$document) {
                    throw new \RuntimeException("Document not found or stored.");
                }

                Log::info('Document created successfully', ['document_id' => $document->id]);

                $serviceClass = processor($service)->getResolvedService();

                if (!$serviceClass) {
                    throw new \RuntimeException("Service not resolved.");
                }

                $resource->refresh();

                if ($resource->document) {
                    $serviceClass->resolveDocumentAmount($resource['id']);
                    $serviceClass->bindRelatedDocuments($resource->document, $resource, "batched");
                }

                if ($mode === "store" && !empty($uploads)) {
                    $this->uploadRepository->uploadMany(
                        $uploads,
                        $document->id,
                        Document::class
                    );
                }
            }); // This retries the transaction 3 times

            // Send notifications to relevant stakeholders
            try {
                // Get current tracker (first stage for new documents)
                $currentTracker = $this->findCurrentTracker($document->pointer, $trackers);

                // Determine notification type and action status based on mode
                $notificationType = $mode === 'store' ? 'document_created' : 'document_updated';
                $actionStatus = $mode === 'store' ? 'created' : 'updated';

                // Create notification context
                $context = NotificationContext::from($document, [
                    'documentId' => $document->id,
                    'currentTracker' => $currentTracker,
                    'previousTracker' => null, // No previous tracker for creation/update
                    'trackers' => $trackers,
                    'loggedInUser' => [
                        'id' => Auth::id(),
                        'firstname' => auth()->user()->firstname ?? 'User',
                        'department_id' => auth()->user()->department_id ?? 0,
                        'surname' => auth()->user()->surname ?? '',
                        'email' => auth()->user()->email ?? ''
                    ],
                    'actionStatus' => $actionStatus,
                    'watchers' => $watchers,
                    'meta_data' => $metaData,
                ]);

                // Validate context before sending
                if ($context->isValid()) {
                    Log::info('DocumentBuilderController: Sending notification for document ' . $mode, [
                        'document_id' => $document->id,
                        'document_ref' => $document->ref,
                        'action_status' => $actionStatus,
                        'notification_type' => $notificationType,
                        'mode' => $mode
                    ]);

                    // Send notification
                    $this->notificationService->notify($context);

                    Log::info('DocumentBuilderController: Notification sent successfully', [
                        'document_id' => $document->id,
                        'mode' => $mode,
                        'notification_type' => $notificationType
                    ]);
                } else {
                    Log::warning('DocumentBuilderController: Invalid notification context for ' . $mode, [
                        'document_id' => $document->id,
                        'missing_fields' => $context->getMissingFields()
                    ]);
                }

            } catch (\Throwable $e) {
                // Log but don't fail the request
                Log::error('DocumentBuilderController: Failed to send notification for document ' . $mode, [
                    'document_id' => $document->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            return $this->success(new DocumentResource($document), 'Document generated successfully.');

        } catch (\Throwable $e) {
            return $this->error(null, $e->getMessage(), 422);
        }
    }

    public function process(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'action_id' => 'required|integer|exists:document_actions,id',
            'action_status' => 'required|string',
            'body' => 'required|array',
            'currentPointer' => 'required|string',
            'document_id' => 'required|integer|exists:documents,id',
            'metadata' => 'required',
            'service' => 'required|string',
            'status' => 'required|string',
            'user_id' => 'required|integer|exists:users,id',
            'trackers' => 'required|array',
        ]);

        if ($validator->fails()) {
            $error = new ValidationErrors($validator->errors());
            return $this->error($error->getValidationErrors(), $error->getMessage(), 422);
        }

        try {
            $userId                 = (int) $request->input('user_id');
            $documentId             = (int) $request->input('document_id');
            $documentActionId       = (int) $request->input('action_id');
            $actionStatus           = (string) $request->input('action_status');
            $service                = (string) $request->input('service');
            $status                 = (string) $request->input('status');
            $currentPointer         = (string) $request->input('currentPointer');
            $metaData               = (array)  $request->input('metadata', []);
            $contents               = (array)  $request->input('body', []);
            $document_activities    = (array)  $request->input('document_activities', []);
            $trackers               = (array) $request->input('trackers', []);

            $document = $this->documentRepository->update($documentId, [
                'document_action_id' => $documentActionId,
                'status' => $status,
                'pointer' => $currentPointer,
                'contents' => $contents,
                'activities' => $document_activities,
            ]);

            if (!$document) {
                return $this->error(null, 'Document not found or could not be updated.', 404);
            }

            if ($actionStatus === 'complete') {
                $document->is_completed = true;
                // Check the Document Category metadata activities
                // if it has activities, then we need to process the activities
                $postProcesses = $document->documentCategory->meta_data['activities'] ?? [];
                if (!empty($postProcesses)) {
                    $activities = collect($postProcesses);
                    $process = $activities->firstWhere('document_action_id', $documentActionId);

                    // Workflow takes priority over Actions
                    if (isset($process['workflow_id']) && $process['workflow_id'] > 0) {
                        $workflow = $this->workflowRepository->find($process['workflow_id']);
                        if ($workflow) {
                            $document->workflow_id = $workflow->id;
                            $document->save();

                            // Get the document action for context
                            $documentAction = $this->documentActionRepository->find($documentActionId);

                            // Trigger Workflow with action context!
                            processor()->executeWorkflowAction('first', $document, $documentAction);
                        }
                    } else if ($process['trigger_action_id'] && $process['trigger_action_id'] > 0) {
                        $action = $this->documentActionRepository->find($process['trigger_action_id']);

                        if ($action) {
                            $document->status = $action->draft_status;
                            $document->save();
                        }
                    }
                }

                $document->save();
            }

            $currentTracker = $this->findCurrentTracker($document->pointer, $trackers);

            // Process workflow notifications
            try {
                $context = NotificationContext::from($document, [
                    'documentId' => $document->id,
                    'currentTracker' => $currentTracker,
                    'previousTracker' => $this->findPreviousTracker($currentTracker, $trackers),
                    'trackers' => $trackers,
                    'loggedInUser' => [
                        'id' => $userId,
                        'firstname' => auth()->user()->firstname ?? 'User',
                        'department_id' => auth()->user()->department_id ?? 0,
                        'surname' => auth()->user()->surname ?? '',
                        'email' => auth()->user()->email ?? ''
                    ],
                    'actionStatus' => $actionStatus,
                    'watchers' => $document->watchers,
                    'meta_data' => $document->meta_data,
                ]);

                // Validate notification context before processing
                if (!$context->isValid()) {
                    $missingFields = $context->getMissingFields();
                    Log::error('DocumentBuilderController: Invalid notification context', [
                        'document_id' => $document->id,
                        'missing_fields' => $missingFields,
                        'context_data' => [
                            'document_id' => $context->documentId,
                            'action_status' => $context->actionStatus,
                            'current_tracker_identifier' => $context->currentTracker['identifier'] ?? 'missing',
                            'logged_in_user_id' => $context->loggedInUser['id'] ?? 'missing',
                            'tracker_count' => count($context->trackers),
                        ]
                    ]);
                    return $this->error(null, 'Invalid notification context: ' . implode(', ', $missingFields), 422);
                }

                Log::info('DocumentBuilderController: Notification context validated successfully', [
                    'document_id' => $context->documentId,
                    'document_ref' => $context->documentRef,
                    'document_title' => $context->documentTitle,
                    'action_status' => $context->actionStatus,
                    'current_tracker' => $context->currentTracker['identifier'] ?? 'unknown',
                    'previous_tracker' => $context->previousTracker['identifier'] ?? 'none',
                    'department_id' => $context->departmentId,
                    'user_id' => $context->userId,
                ]);

                $this->notificationService->notify($context);

                Log::info('DocumentBuilderController: Notification service called successfully', [
                    'document_id' => $document->id,
                    'action_status' => $actionStatus
                ]);

            } catch (\Throwable $e) {
                // Log the error but don't fail the main process
                Log::error('DocumentBuilderController: Failed to process workflow notifications', [
                    'document_id' => $document->id,
                    'action_status' => $actionStatus,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            return $this->success(new DocumentResource($document), 'Document processed successfully.');

        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function systemFlow(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service' => 'required|string|max:255',
            'method' => 'required|string|max:255',
            'mode' => 'required|string|max:255|in:store,update,destroy',
            'data' => 'required',
            'document_id' => 'required|integer|exists:documents,id',
            'document_draft_id' => 'required|integer|exists:document_drafts,id',
            'document_action_id' => 'required|integer|exists:document_actions,id',
            'progress_tracker_id' => 'required|integer|exists:progress_trackers,id',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Please fix the following errors', 422);
        }

        try {
            // Extract validated data
            $service = $request->input('service');
            $method = $request->input('method');
            $mode = $request->input('mode');
            $data = $request->input('data');
            $documentId = $request->input('document_id');
            $documentDraftId = $request->input('document_draft_id');
            $documentActionId = $request->input('document_action_id');
            $progressTrackerId = $request->input('progress_tracker_id');

            // Get the document and related models
            $document = $this->documentRepository->find($documentId);
            $documentDraft = DocumentDraft::find($documentDraftId);
            $documentAction = $this->documentActionRepository->find($documentActionId);
            $progressTracker = ProgressTracker::find($progressTrackerId);

            if (!$document || !$documentDraft || !$documentAction || !$progressTracker) {
                return $this->error(null, 'One or More Required models not found', 404);
            }

            DB::transaction(function () use (
                $service, $method, $mode, $data, $document, $documentDraft,
                $documentAction, $progressTracker
            ) {
                // 1. Resolve Service
                $serviceClass = processor($service)->getResolvedService();

                if (!$serviceClass) {
                    throw new \RuntimeException("Service '{$service}' not resolved.");
                }

                // 2. Verify Method Exists in serviceClass
                if (!method_exists($serviceClass, $method)) {
                    throw new \RuntimeException("Method '{$method}' does not exist in service class.");
                }

                // 3. If the method exists send $request->data into the method
                $result = $serviceClass->{$method}($data, $mode);

                // 4. Trigger the workflow using ControlPanel
                $workflow = $progressTracker->workflow;
                if ($workflow) {
                    $controlPanel = new ControlPanel(
                        app(ServiceResolverInterface::class),
                        $document,
                        $workflow,
                        $documentAction
                    );

                    // Determine workflow action based on document action status
                    $workflowAction = $this->determineWorkflowAction($mode, $result, $documentAction);

                    switch ($workflowAction) {
                        case 'next':
                            $controlPanel->next();
                            break;
                        case 'wait':
                            $controlPanel->wait();
                            break;
                        case 'end':
                            $controlPanel->end();
                            break;
                        case 'rollback':
                            $controlPanel->rollback();
                            break;
                        case 'reject':
                            $controlPanel->reject();
                            break;
                        default:
                            // No workflow action needed
                            break;
                    }
                }

                // 5. Carry out document updates using the $request->document_id
                $documentUpdates = $this->prepareDocumentUpdates($result, $mode);
                if (!empty($documentUpdates)) {
                    $this->documentRepository->update($document->id, $documentUpdates);
                }

                // 6. Carry out document draft updates
                $draftUpdates = $this->prepareDraftUpdates($result, $mode);
                if (!empty($draftUpdates)) {
                    $documentDraft->update($draftUpdates);
                }

                // 7. Notify Recipients
                try {
                    $this->triggerNotification($document, $documentAction, $progressTracker, $workflowAction ?? null);
                } catch (\Throwable $e) {
                    Log::error('Failed to send notifications', [
                        'document_id' => $document->id,
                        'error' => $e->getMessage()
                    ]);
                }
            });

            // 8. Return document
            $document->refresh();
            return $this->success(new DocumentResource($document), 'System flow processed successfully.');

        } catch (\Throwable $e) {
            Log::error('SystemFlow error', [
                'request' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Determine the appropriate workflow action based on document action status
     */
    private function determineWorkflowAction(string $mode, $result, DocumentAction $documentAction): ?string
    {
        // Check if result contains workflow action (highest priority)
        if (is_array($result) && isset($result['workflow_action'])) {
            return $result['workflow_action'];
        }

        // Map document action status to workflow actions
        return match ($documentAction->action_status) {
            'passed' => 'next',
            'failed' => 'reject',
            'cancelled', 'complete' => 'end',
            'reversed' => 'rollback',
            default => 'wait',
        };
    }

    /**
     * Prepare document updates based on result and mode
     */
    private function prepareDocumentUpdates($result, string $mode): array
    {
        $updates = [];

        if (is_array($result)) {
            // Update document status if provided
            if (isset($result['status'])) {
                $updates['status'] = $result['status'];
            }

            // Update document contents if provided
            if (isset($result['contents'])) {
                $updates['contents'] = $result['contents'];
            }

            // Update document meta data if provided
            if (isset($result['meta_data'])) {
                $updates['meta_data'] = $result['meta_data'];
            }
        }

        return $updates;
    }

    /**
     * Prepare document draft updates based on result and mode
     */
    private function prepareDraftUpdates($result, string $mode): array
    {
        $updates = [
            'operator_id' => Auth::id(),
            'updated_at' => now(),
        ];

        if (is_array($result)) {
            // Update draft status if provided
            if (isset($result['draft_status'])) {
                $updates['status'] = $result['draft_status'];
            }

            // Update draft data if provided
            if (isset($result['draft_data'])) {
                $updates['data'] = $result['draft_data'];
            }
        }

        return $updates;
    }

    /**
     * Trigger notifications for workflow changes
     * @throws \Throwable
     */
    private function triggerNotification(
        Document $document,
        DocumentAction $documentAction,
        ProgressTracker $progressTracker,
        ?string $workflowAction
    ): void
    {
        try {
            // Get all workflow trackers as proper array format
            $trackers = $document->workflow?->trackers?->map(function ($tracker) {
                return [
                    'id' => $tracker->id,
                    'identifier' => $tracker->identifier,
                    'label' => $tracker->label ?? 'Stage',
                    'order' => $tracker->order,
                    'user_id' => $tracker->user_id ?? 0,
                    'group_id' => $tracker->group_id ?? 0,
                    'department_id' => $tracker->department_id ?? 0,
                    'workflow_stage_id' => $tracker->workflow_stage_id,
                    'permission' => $tracker->permission ?? 'rw',
                ];
            })->toArray() ?? [];

            if (empty($trackers)) {
                Log::warning('DocumentBuilderController: No trackers found for systemFlow notification', [
                    'document_id' => $document->id,
                    'workflow_id' => $document->workflow_id ?? 'none'
                ]);
                return;
            }

            // Find current and previous trackers using proper array format
            $currentTracker = $this->findCurrentTracker($document->pointer, $trackers);
            $previousTracker = $this->findPreviousTracker($currentTracker, $trackers);

            // Use actual document action status
            $actionStatus = $documentAction->action_status;

            Log::info('DocumentBuilderController: Preparing systemFlow notification', [
                'document_id' => $document->id,
                'action_status' => $actionStatus,
                'workflow_action' => $workflowAction,
                'current_tracker' => $currentTracker['identifier'] ?? 'unknown',
                'previous_tracker' => $previousTracker['identifier'] ?? 'none'
            ]);

            // Create notification context
            $context = NotificationContext::from($document, [
                'documentId' => $document->id,
                'currentTracker' => $currentTracker,
                'previousTracker' => $previousTracker,
                'trackers' => $trackers,
                'loggedInUser' => [
                    'id' => Auth::id(),
                    'firstname' => Auth::user()->firstname ?? 'User',
                    'department_id' => Auth::user()->department_id ?? 0,
                    'surname' => Auth::user()->surname ?? '',
                    'email' => Auth::user()->email ?? ''
                ],
                'actionStatus' => $actionStatus,  // Use actual action status!
                'watchers' => $document->watchers ?? [],
                'meta_data' => $document->meta_data ?? [],
            ]);

            // Validate before sending
            if ($context->isValid()) {
                Log::info('DocumentBuilderController: Sending systemFlow notification', [
                    'document_id' => $document->id,
                    'document_ref' => $context->documentRef,
                    'action_status' => $actionStatus,
                    'workflow_action' => $workflowAction
                ]);

                $this->notificationService->notify($context);

                Log::info('DocumentBuilderController: systemFlow notification sent successfully', [
                    'document_id' => $document->id,
                    'action_status' => $actionStatus
                ]);
            } else {
                Log::warning('DocumentBuilderController: Invalid notification context in systemFlow', [
                    'document_id' => $document->id,
                    'missing_fields' => $context->getMissingFields()
                ]);
            }

        } catch (\Throwable $e) {
            Log::error('DocumentBuilderController: Failed to trigger systemFlow notification', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't re-throw to avoid breaking the main flow
        }
    }
}
