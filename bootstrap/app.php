<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Suppress deprecation warnings from being displayed in HTTP responses
// They will still be logged but won't pollute JSON/HTTP responses
if (PHP_SAPI !== 'cli') {
    // For web requests, suppress display of deprecation warnings
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\DebugCsrfTokenMiddleware::class
        ]);

        // Ensure frontend requests are treated as stateful (handles CSRF)
        $middleware->api(prepend: [
            \App\Http\Middleware\CustomEnsureFrontendRequestsAreStateful::class,
        ]);

        // Add necessary middlewares, ensuring correct order
        $middleware->api(append: [
            \App\Http\Middleware\Cors::class,
            \App\Http\Middleware\ForceJsonResponse::class,
            \App\Http\Middleware\HandleFormDataPutRequests::class,
//            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        ]);

        // Alias middleware for easy reference
        $middleware->alias([
            'cors' => \App\Http\Middleware\Cors::class,
            'verify.identity' => \App\Http\Middleware\VerifyIdentityMarker::class,
            'log.request' => \App\Http\Middleware\LogApiRequest::class,
            'json.response' => \App\Http\Middleware\ForceJsonResponse::class,
            'handle.formdata' => \App\Http\Middleware\HandleFormDataPutRequests::class,
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Ensure deprecation warnings don't appear in HTTP responses
        // They will be logged to the log files instead
        if (PHP_SAPI !== 'cli') {
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');
        }
    })->create();
