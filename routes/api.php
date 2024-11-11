<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['cors', 'json.response']], function () {
//    Route::post('/login', [\App\Http\Controllers\AuthenticateUserController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('departments', \App\Http\Controllers\DepartmentController::class);
        Route::apiResource('roles', \App\Http\Controllers\RoleController::class);
        Route::apiResource('users', \App\Http\Controllers\UserController::class);
        Route::apiResource('pages', \App\Http\Controllers\PageController::class);
        Route::apiResource('gradeLevels', \App\Http\Controllers\GradeLevelController::class);
        Route::apiResource('permissions', \App\Http\Controllers\PermissionController::class);
    });
});
