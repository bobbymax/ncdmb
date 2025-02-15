<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DebugCsrfTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('Incoming CSRF Token Debug:', [
            'Session Token' => session()->token(),
            'Request Header X-CSRF-TOKEN' => $request->header('X-CSRF-TOKEN'),
            'Request Header X-XSRF-TOKEN' => $request->header('X-XSRF-TOKEN'),
            'Request Input _token' => $request->input('_token'),
            'Cookies' => $request->cookies->all(),
        ]);

        return $next($request);
    }
}
