<?php

use App\Http\Controllers\DocumentBuilderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return new \App\Http\Resources\AuthUserResource($request->user());
});

Route::middleware(['auth:sanctum'])->get('/auth-token', function (Request $request) {
    return response()->json(['token' => $request->user()->currentAccessToken()->plainTextToken]);
});

Route::group(['middleware' => ['cors', 'json.response']], function () {
    Route::get('storage/{path}', function ($path) {
        $file = \Illuminate\Support\Facades\Storage::disk('public')->get($path);
        $type = \Illuminate\Support\Facades\Storage::disk('public')->mimeType($path);

        return response($file, 200)->header('Content-Type', $type);
    })->where('path', '.*');

    Route::post('/login', [\App\Http\Controllers\AuthApiController::class, 'login']);

    // 2FA Verification (No auth required - happens during login)
    Route::post('2fa/verify', [\App\Http\Controllers\Auth\TwoFactorController::class, 'verify']);

    // Ping server for network monitoring
    Route::get('/ping', function (Request $request) {
        return response()->json(['ping' => true]);
    });

    Route::post('auth/refresh', [\App\Http\Controllers\AuthApiController::class, 'refreshToken']);


    Route::middleware(['cors', 'auth:sanctum', 'verify.identity', 'log.request'])->group(function () {
        Route::post('/logout', [\App\Http\Controllers\AuthApiController::class, 'logout']);

        // 2FA Management Routes (requires authentication)
        Route::prefix('2fa')->group(function () {
            Route::get('status', [\App\Http\Controllers\Auth\TwoFactorController::class, 'status']);
            Route::post('generate', [\App\Http\Controllers\Auth\TwoFactorController::class, 'generate']);
            Route::post('confirm', [\App\Http\Controllers\Auth\TwoFactorController::class, 'confirm']);
            Route::post('disable', [\App\Http\Controllers\Auth\TwoFactorController::class, 'disable']);
        });

        // Inbound AI analysis trigger
        Route::post('query', \App\Http\Controllers\QueryBuilderController::class);
        Route::post('inbounds/{id}/analyze', [\App\Http\Controllers\InboundController::class, 'analyze']);
        Route::post('configuration/imports/{resource}', [\App\Http\Controllers\ImportController::class, 'getResource']);
        Route::get('apiServices', [\App\Http\Controllers\ApiServiceController::class, 'index']);
        Route::get('imports', [\App\Http\Controllers\ApiServiceController::class, 'imports']);
        Route::get('fetch/{resource}/editor/{trackerId}', [\App\Http\Controllers\ApiServiceController::class, 'fetchEditor']);
        Route::get('committment/funds/{fund}', [\App\Http\Controllers\FundController::class, 'totalCurrentCommittment']);
        Route::get('linked/documents/{parentDocumentId}', [\App\Http\Controllers\DocumentController::class, 'getLinkedDocuments']);
        Route::get('resource/{service}/collection', [\App\Http\Controllers\ServiceWorkerController::class, 'resourceCollection']);
        Route::get('documents/ref/{ref}', [\App\Http\Controllers\ServiceWorkerController::class, 'fetchDocumentUsingRef'])->where('ref', '.*');
        Route::post('generate/document', [\App\Http\Controllers\DocumentBuilderController::class, 'buildDocument']);
        Route::post('process/document', [\App\Http\Controllers\DocumentBuilderController::class, 'process']);
        Route::get('collated/{status}/documents', [\App\Http\Controllers\DocumentController::class, 'queuedDocuments']);
        Route::post('process/handle/request', [\App\Http\Controllers\GoogleApiController::class, 'assign']);
        Route::get('users/{groupId}/{departmentId}', [\App\Http\Controllers\UserController::class, 'grouping']);

        Route::get('chat-token', [\App\Http\Controllers\AuthApiController::class, 'getChatToken']);
        Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index']);
        Route::post('document-category/signatories', [\App\Http\Controllers\DocumentCategoryController::class, 'addSignatories']);

        // Google Api Endpoints
        Route::get('distance', [\App\Http\Controllers\GoogleApiController::class, 'getDistanceInKm']);
        // End Google Api Endpoints

        Route::put('claim/updates/{claimId}', [App\Http\Controllers\ClaimController::class, 'alter']);
        Route::put('service-workers/{service}', [App\Http\Controllers\ServiceWorkerController::class, 'handleService']);
        Route::post('process/request/data', [\App\Http\Controllers\ServiceWorkerController::class, 'handleProcessServiceData']);
        Route::get('authorised/users/{group}/{department}', [\App\Http\Controllers\SignatureRequestController::class, 'authorisedUsers']);
        Route::get('staff/claims/{userId}/{claimId}', [\App\Http\Controllers\UserController::class, 'claims']);
        Route::get('document/documentDrafts/{status}', [\App\Http\Controllers\DocumentDraftController::class, 'drafts']);
        Route::get('resolvers/{status}/{access_level}/{user_column}/{draftScope}', [\App\Http\Controllers\ApiServiceController::class, 'records']);
        Route::get('group/ledgers', [\App\Http\Controllers\LedgerController::class, 'getLedgers']);
        Route::post('threads/create-and-message', [\App\Http\Controllers\ThreadController::class, 'saveAndSendMessage']);
        Route::get('threads/{thread}/conversations', [\App\Http\Controllers\ThreadController::class, 'conversations']);
        Route::post('threads/{thread}/conversations', [\App\Http\Controllers\ThreadController::class, 'send']);
        Route::post('configure/settings', [\App\Http\Controllers\SettingController::class, 'updateConfig']);
        Route::post('document/processor', [\App\Http\Controllers\DocumentBuilderController::class, 'systemFlow']);

        Route::apiResource('departments', \App\Http\Controllers\DepartmentController::class);
        Route::apiResource('roles', \App\Http\Controllers\RoleController::class);
        Route::apiResource('users', \App\Http\Controllers\UserController::class);
        Route::apiResource('pages', \App\Http\Controllers\PageController::class);
        Route::apiResource('gradeLevels', \App\Http\Controllers\GradeLevelController::class);
        Route::apiResource('permissions', \App\Http\Controllers\PermissionController::class);
        Route::apiResource('workflows', \App\Http\Controllers\WorkflowController::class);
        Route::apiResource('workflowStages', \App\Http\Controllers\WorkflowStageController::class);
        Route::apiResource('groups', \App\Http\Controllers\GroupController::class);
        Route::apiResource('workflowStageCategories', \App\Http\Controllers\WorkflowStageCategoryController::class);
        Route::apiResource('progressTrackers', \App\Http\Controllers\ProgressTrackerController::class);
        Route::apiResource('carders', \App\Http\Controllers\CarderController::class);
        Route::apiResource('locations', \App\Http\Controllers\LocationController::class);
        Route::apiResource('signatureRequests', \App\Http\Controllers\SignatureRequestController::class);
        Route::apiResource('signatories', \App\Http\Controllers\SignatoryController::class);
        Route::apiResource('resourceEditors', \App\Http\Controllers\ResourceEditorController::class);
        Route::apiResource('templates', \App\Http\Controllers\TemplateController::class);
        Route::apiResource('blocks', \App\Http\Controllers\BlockController::class);
        Route::apiResource('thresholds', \App\Http\Controllers\ThresholdController::class);
        Route::apiResource('projectCategories', \App\Http\Controllers\ProjectCategoryController::class);
        Route::apiResource('projectPrograms', \App\Http\Controllers\ProjectProgramController::class);
        Route::get('projectPrograms/{id}/phases', [\App\Http\Controllers\ProjectProgramController::class, 'phases']);
        Route::post('projectPrograms/{id}/recalculate', [\App\Http\Controllers\ProjectProgramController::class, 'recalculate']);
        Route::apiResource('projects', \App\Http\Controllers\ProjectController::class);
        Route::apiResource('invoices', \App\Http\Controllers\InvoiceController::class);
        Route::apiResource('invoiceItems', \App\Http\Controllers\InvoiceItemController::class);
        Route::apiResource('threads', \App\Http\Controllers\ThreadController::class);
        Route::apiResource('settings', \App\Http\Controllers\SettingController::class);
        Route::apiResource('processCards', \App\Http\Controllers\ProcessCardController::class);

        Route::apiResource('documentTypes', \App\Http\Controllers\DocumentTypeController::class);
        Route::apiResource('documentRequirements', \App\Http\Controllers\DocumentRequirementController::class);
        Route::apiResource('documentCategories', \App\Http\Controllers\DocumentCategoryController::class);
        Route::apiResource('documentActions', \App\Http\Controllers\DocumentActionController::class);
        Route::apiResource('documents', \App\Http\Controllers\DocumentController::class);
        Route::apiResource('documentDrafts', \App\Http\Controllers\DocumentDraftController::class);
        Route::apiResource('documentComments', \App\Http\Controllers\DocumentCommentController::class);
        Route::apiResource('documentUpdates', \App\Http\Controllers\DocumentUpdateController::class);
        Route::apiResource('mailingLists', \App\Http\Controllers\MailingListController::class);
        Route::apiResource('fileTemplates', \App\Http\Controllers\FileTemplateController::class);
        Route::apiResource('uploads', \App\Http\Controllers\UploadController::class);
        Route::apiResource('widgets', \App\Http\Controllers\WidgetController::class);
        Route::apiResource('chartOfAccounts', \App\Http\Controllers\ChartOfAccountController::class);
        Route::apiResource('ledgers', \App\Http\Controllers\LedgerController::class);
        Route::apiResource('payments', \App\Http\Controllers\PaymentController::class);
        Route::apiResource('journalTypes', \App\Http\Controllers\JournalTypeController::class);
        Route::apiResource('documentPanels', \App\Http\Controllers\DocumentPanelController::class);
        Route::apiResource('inbounds', \App\Http\Controllers\InboundController::class);
        Route::apiResource('inboundInstructions', \App\Http\Controllers\InboundInstructionController::class);

        // Monitoring and Evaluations Module
        Route::apiResource('companies', \App\Http\Controllers\CompanyController::class);
        Route::apiResource('companyRepresentatives', \App\Http\Controllers\CompanyRepresentativeController::class);
        Route::apiResource('schedules', \App\Http\Controllers\ScheduleController::class);
        Route::apiResource('operations', \App\Http\Controllers\OperationController::class);

        // Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index']);
            Route::get('/unread', [\App\Http\Controllers\NotificationController::class, 'unreadCount']);
            Route::post('/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
            Route::post('/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
            Route::delete('/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy']);
        });

        // AI Services (Direct API - legacy)
//        Route::post('ai/analyze-inbound', [\App\Http\Controllers\AIController::class, 'analyzeInboundDocument']);

        Route::apiResource('budgetHeads', \App\Http\Controllers\BudgetHeadController::class);
        Route::apiResource('budgetCodes', \App\Http\Controllers\BudgetCodeController::class);
        Route::apiResource('subBudgetHeads', \App\Http\Controllers\SubBudgetHeadController::class);
        Route::apiResource('funds', \App\Http\Controllers\FundController::class);
        Route::apiResource('expenditures', \App\Http\Controllers\ExpenditureController::class);
        Route::apiResource('paymentBatches', \App\Http\Controllers\PaymentBatchController::class);

        Route::apiResource('allowances', \App\Http\Controllers\AllowanceController::class);
        Route::apiResource('remunerations', \App\Http\Controllers\RemunerationController::class);
        Route::apiResource('claims', \App\Http\Controllers\ClaimController::class)->middleware('handle.formdata');
        Route::apiResource('trips', \App\Http\Controllers\TripController::class);
        Route::apiResource('expenses', \App\Http\Controllers\ExpenseController::class);
        Route::apiResource('cities', \App\Http\Controllers\CityController::class);
        Route::apiResource('tripCategories', \App\Http\Controllers\TripCategoryController::class);
        Route::apiResource('entities', \App\Http\Controllers\EntityController::class);
        Route::apiResource('vendors', \App\Http\Controllers\VendorController::class);

        // Inventory Module
        Route::apiResource('requisitions', \App\Http\Controllers\RequisitionController::class);
        Route::apiResource('inventory-locations', \App\Http\Controllers\InventoryLocationController::class);
        Route::apiResource('inventory-balances', \App\Http\Controllers\InventoryBalanceController::class);
        Route::apiResource('inventory-batches', \App\Http\Controllers\InventoryBatchController::class);
        Route::apiResource('inventory-transactions', \App\Http\Controllers\InventoryTransactionController::class);
        Route::apiResource('inventory-issues', \App\Http\Controllers\InventoryIssueController::class);
        Route::apiResource('inventory-issue-items', \App\Http\Controllers\InventoryIssueItemController::class);
        Route::apiResource('inventory-returns', \App\Http\Controllers\InventoryReturnController::class);
        Route::apiResource('inventory-adjustments', \App\Http\Controllers\InventoryAdjustmentController::class);
        Route::apiResource('inventory-receipts', \App\Http\Controllers\InventoryReceiptController::class);
        Route::apiResource('inventory-receipt-items', \App\Http\Controllers\InventoryReceiptItemController::class);
        Route::apiResource('inventory-transfers', \App\Http\Controllers\InventoryTransferController::class);
        Route::apiResource('inventory-transfer-items', \App\Http\Controllers\InventoryTransferItemController::class);
        Route::apiResource('inventory-reservations', \App\Http\Controllers\InventoryReservationController::class);
        Route::apiResource('inventory-valuations', \App\Http\Controllers\InventoryValuationController::class);
        Route::apiResource('products', \App\Http\Controllers\ProductController::class);
        Route::apiResource('productCategories', \App\Http\Controllers\ProductCategoryController::class);
        Route::apiResource('productBrands', \App\Http\Controllers\ProductBrandController::class);
        Route::apiResource('measurementTypes', \App\Http\Controllers\MeasurementTypeController::class);


        // Procurement Module Routes
        Route::prefix('procurement')->group(function () {
            // Bid Invitations
            Route::apiResource('bid-invitations', \App\Http\Controllers\ProjectBidInvitationController::class);
            Route::post('bid-invitations/{id}/publish', [\App\Http\Controllers\ProjectBidInvitationController::class, 'publish']);
            Route::post('bid-invitations/{id}/close', [\App\Http\Controllers\ProjectBidInvitationController::class, 'close']);

            // Bids
            Route::apiResource('bids', \App\Http\Controllers\ProjectBidController::class);
            Route::post('bids/{id}/open', [\App\Http\Controllers\ProjectBidController::class, 'open']);
            Route::post('bids/{id}/evaluate', [\App\Http\Controllers\ProjectBidController::class, 'evaluate']);
            Route::post('bids/{id}/recommend', [\App\Http\Controllers\ProjectBidController::class, 'recommend']);
            Route::post('bids/{id}/disqualify', [\App\Http\Controllers\ProjectBidController::class, 'disqualify']);

            // Bid Evaluations
            Route::apiResource('evaluations', \App\Http\Controllers\ProjectBidEvaluationController::class);
            Route::post('evaluations/{id}/submit', [\App\Http\Controllers\ProjectBidEvaluationController::class, 'submit']);
            Route::post('evaluations/{id}/approve', [\App\Http\Controllers\ProjectBidEvaluationController::class, 'approve']);

            // Evaluation Committees
            Route::apiResource('committees', \App\Http\Controllers\ProjectEvaluationCommitteeController::class);
            Route::post('committees/{id}/dissolve', [\App\Http\Controllers\ProjectEvaluationCommitteeController::class, 'dissolve']);

            // Audit Trails
            Route::get('audit-trails', [\App\Http\Controllers\ProcurementAuditTrailController::class, 'index']);
            Route::get('audit-trails/project/{project}', [\App\Http\Controllers\ProcurementAuditTrailController::class, 'byProject']);
        });

        // Legal Cycle Module Routes
        Route::prefix('legal')->group(function () {
            // Legal Reviews
            Route::apiResource('reviews', \App\Http\Controllers\LegalReviewController::class);
            Route::post('reviews/{id}/approve', [\App\Http\Controllers\LegalReviewController::class, 'approve']);
            Route::post('reviews/{id}/reject', [\App\Http\Controllers\LegalReviewController::class, 'reject']);
            Route::get('reviews/project/{projectId}', [\App\Http\Controllers\LegalReviewController::class, 'byProject']);
            Route::get('reviews/contract/{contractId}', [\App\Http\Controllers\LegalReviewController::class, 'byContract']);

            // Legal Clearances
            Route::apiResource('clearances', \App\Http\Controllers\LegalClearanceController::class);
            Route::post('clearances/{id}/grant', [\App\Http\Controllers\LegalClearanceController::class, 'grant']);
            Route::post('clearances/{id}/revoke', [\App\Http\Controllers\LegalClearanceController::class, 'revoke']);
            Route::get('clearances/contract/{contractId}', [\App\Http\Controllers\LegalClearanceController::class, 'byContract']);

            // Contract Variations
            Route::apiResource('variations', \App\Http\Controllers\ContractVariationController::class);
            Route::post('variations/{id}/approve', [\App\Http\Controllers\ContractVariationController::class, 'approve']);
            Route::post('variations/{id}/reject', [\App\Http\Controllers\ContractVariationController::class, 'reject']);
            Route::get('variations/contract/{contractId}', [\App\Http\Controllers\ContractVariationController::class, 'byContract']);

            // Legal Compliance Checks
            Route::apiResource('compliance-checks', \App\Http\Controllers\LegalComplianceCheckController::class);
            Route::get('compliance-checks/contract/{contractId}', [\App\Http\Controllers\LegalComplianceCheckController::class, 'byContract']);

            // Legal Documents
            Route::apiResource('documents', \App\Http\Controllers\LegalDocumentController::class)->names([
                'index' => 'legal.documents.index',
                'show' => 'legal.documents.show',
                'store' => 'legal.documents.store',
                'update' => 'legal.documents.update',
                'destroy' => 'legal.documents.destroy',
            ]);
            Route::post('documents/{id}/sign', [\App\Http\Controllers\LegalDocumentController::class, 'sign'])->name('legal.documents.sign');
            Route::get('documents/contract/{contractId}', [\App\Http\Controllers\LegalDocumentController::class, 'byContract'])->name('legal.documents.byContract');

            // Contract Disputes
            Route::apiResource('disputes', \App\Http\Controllers\ContractDisputeController::class);
            Route::post('disputes/{id}/resolve', [\App\Http\Controllers\ContractDisputeController::class, 'resolve']);
            Route::post('disputes/{id}/escalate', [\App\Http\Controllers\ContractDisputeController::class, 'escalate']);
            Route::get('disputes/contract/{contractId}', [\App\Http\Controllers\ContractDisputeController::class, 'byContract']);

            // Legal Audit Trails
            Route::get('audit-trails', [\App\Http\Controllers\LegalAuditTrailController::class, 'index']);
            Route::get('audit-trails/contract/{contractId}', [\App\Http\Controllers\LegalAuditTrailController::class, 'byContract']);
            Route::get('audit-trails/project/{projectId}', [\App\Http\Controllers\LegalAuditTrailController::class, 'byProject']);
        });

        // Project Contracts
        Route::apiResource('project-contracts', \App\Http\Controllers\ProjectContractController::class);
        Route::get('project-contracts/project/{projectId}', [\App\Http\Controllers\ProjectContractController::class, 'byProject']);

        Route::apiResource('vendors', \App\Http\Controllers\VendorController::class);
        Route::apiResource('workOrders', \App\Http\Controllers\WorkOrderController::class);
    });
});
