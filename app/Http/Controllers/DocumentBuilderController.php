<?php

namespace App\Http\Controllers;

use App\DTO\DocumentActivityContext;
use App\Http\Resources\DocumentResource;
use App\Jobs\TriggerDocumentActivityEvent;
use App\Models\Document;
use App\Repositories\DocumentRepository;
use App\Repositories\UploadRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Handlers\ValidationErrors;

class DocumentBuilderController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected DocumentRepository $documentRepository,
        protected UploadRepository $uploadRepository
    ) {}

    public function buildDocument(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
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

                // watchers normalization
                'watchers' => 'array',
                'watchers.users' => 'array',
                'watchers.users.*.id' => 'required|integer',
                'watchers.users.*.name' => 'nullable|string',
                'watchers.users.*.email' => 'nullable|email',
                'watchers.groups' => 'array',
                'watchers.groups.*.id' => 'required|integer',
                'watchers.groups.*.name' => 'nullable|string',

                'title' => 'required|string',
                'category.document_type_id' => 'required|integer|exists:document_types,id',
                'mode' => 'required|string|in:store,update',
                'requirements' => 'array',
                'linked_documents' => 'array',
                'performed' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $error = new ValidationErrors($validator->errors());
                return $this->error($error->getValidationErrors(), $error->getMessage(), 422);
            }

            // Extract inputs (strongly typed & defaulted)
            $service        = (string) $request->input('service');
            $metaData       = (array)  $request->input('meta_data', []);
            $actions        = (array)  $request->input('actions', []);
            $trackers       = (array)  $request->input('trackers', []);
            $owner          = $request->input('document_owner');      // expect ['value'=>id,'label'=>name,'email'=>?]
            $department     = $request->input('department_owner');    // expect ['value'=>id, 'label'=>name]
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

            // Normalize watchers structure
            $watchersUsers  = array_values((array)($watchers['users']  ?? []));
            $watchersGroups = array_values((array)($watchers['groups'] ?? []));

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
                $approvalMemo, $metaData, $requirements, $watchers, $uploads, $currentPointer,
                &$document
            ) {
                // Resolve Service & persist resource (keep args tiny; your processor() may already do this)
                $resource = processor()->saveResource([
                    'service' => $service,
                    'content' => $content,
                ], $mode === "update");

                if (!$resource) {
                    throw new \RuntimeException("Resource not found or stored.");
                }

                $departmentId = $department['value'] ?? auth()->user()->department_id;

                // Build Document payload
                $documentData = [
                    'user_id'              => $owner['value'] ?? auth()->id(),
                    'department_id'        => $departmentId,
                    'document_type_id'     => $typeId ?: null,
                    'document_category_id' => $category['id'] ?? null,
                    'document_reference_id'=> $approvalMemo['value'] ?? 0,
                    'fund_id'              => $fund['value'] ?? null,
                    'config'               => $config,
                    'contents'             => $content,
                    'documentable_id'      => $resource['id'],
                    'documentable_type'    => get_class($resource),
                    'ref'                  => app(DocumentRepository::class)->generateRef($departmentId, $resource['code']),
                    'title'                => $title ?: ($resource['title'] ?? 'Title Bar'),
                    'status'               => 'pending',
                    'meta_data'            => $metaData,
                    'uploaded_requirements'=> $requirements,
                    'preferences'          => $watchers['preferences'] ?? [], // keep preferences separate if needed
                    'watchers'             => $watchers,                       // raw watchers snapshot for audit
                    'pointer'              => $currentPointer->identifier,
                    'threads'              => $currentPointer->threads ?? [],
                ];

                $document = app(DocumentRepository::class)->create($documentData);

                if (!$document) {
                    throw new \RuntimeException("Document not found or stored.");
                }

                if (!empty($uploads)) {
                    app(UploadRepository::class)->uploadMany(
                        $uploads,
                        $document->id,
                        Document::class
                    );
                }
            }, 3);

            // Build Notification Context (keep it small & explicit)
            $ctx = new DocumentActivityContext(
                document_id:        $document->id,
                workflow_stage_id:   $workflowStageId,
                action_performed:   $actionPerformed,
                loggedInUser:      $loggedInUser,
                document_owner:     $owner ?? ['value'=>auth()->id(), 'label'=>auth()->user()->name, 'email'=>auth()->user()->email],
                department_owner:   $department ?? [],
                document_ref:       $document->ref,
                document_title:     $document->title,
                service:           $service,
                pointer:    (array) $currentPointer,
                threads: [],
                watchers: $watchers,
                desk_name: $workflowStageName
            );

            // Queue the event dispatch AFTER the DB commit and AFTER the HTTP response
            dispatch(new TriggerDocumentActivityEvent($ctx))
                ->afterCommit()
                ->afterResponse()
                ->onQueue('notifications');

            return $this->success(new DocumentResource($document), 'Document generated successfully.');

        } catch (\Throwable $e) {
            return $this->error(null, $e->getMessage(), 422);
        }
    }
}
