<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['cors', 'json.response']], function () {
    Route::post('/login', [\App\Http\Controllers\AuthApiController::class, 'login']);
    // Ping server for network monitoring
    Route::get('/ping', function (Request $request) {
        return response()->json(['ping' => true]);
    });

    Route::post('auth/refresh', [\App\Http\Controllers\AuthApiController::class, 'refreshToken']);


    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [\App\Http\Controllers\AuthApiController::class, 'logout']);

        Route::apiResource('departments', \App\Http\Controllers\DepartmentController::class);
        Route::apiResource('roles', \App\Http\Controllers\RoleController::class);
        Route::apiResource('users', \App\Http\Controllers\UserController::class);
        Route::apiResource('pages', \App\Http\Controllers\PageController::class);
        Route::apiResource('gradeLevels', \App\Http\Controllers\GradeLevelController::class);
        Route::apiResource('permissions', \App\Http\Controllers\PermissionController::class);
        Route::apiResource('workflows', \App\Http\Controllers\WorkflowController::class);
        Route::apiResource('workflowStages', \App\Http\Controllers\WorkflowStageController::class);
        Route::apiResource('groups', \App\Http\Controllers\GroupController::class);

        Route::apiResource('documentTypes', \App\Http\Controllers\DocumentTypeController::class);
        Route::apiResource('documentRequirements', \App\Http\Controllers\DocumentRequirementController::class);
        Route::apiResource('documentCategories', \App\Http\Controllers\DocumentCategoryController::class);
        Route::apiResource('documentActions', \App\Http\Controllers\DocumentActionController::class);
        Route::apiResource('documents', \App\Http\Controllers\DocumentController::class);
        Route::apiResource('documentDrafts', \App\Http\Controllers\DocumentDraftController::class);
        Route::apiResource('documentComments', \App\Http\Controllers\DocumentCommentController::class);
    });
});
