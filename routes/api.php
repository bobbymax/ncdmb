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
    // Ping server for network monitoring
    Route::get('/ping', function (Request $request) {
        return response()->json(['ping' => true]);
    });

    Route::post('auth/refresh', [\App\Http\Controllers\AuthApiController::class, 'refreshToken']);


    Route::middleware(['auth:sanctum', 'verify.identity', 'log.request'])->group(function () {
        Route::post('/logout', [\App\Http\Controllers\AuthApiController::class, 'logout']);

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

        Route::get('chat-token', [\App\Http\Controllers\AuthApiController::class, 'getChatToken']);
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
    });
});
