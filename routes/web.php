<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/test-session', function (\Illuminate\Http\Request $request) {
    return response()->json([
        'XSRF-TOKEN' => $request->header('X-CSRF-TOKEN'),
        'session_data' => session()->all()
    ]);
});

require __DIR__.'/auth.php';
