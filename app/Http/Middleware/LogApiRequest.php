<?php

namespace App\Http\Middleware;

use App\Events\ApiRequestLogged;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = Auth::user();
        $logData = [
            'user_id' => $user ? $user->id : null,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'frontend_url' => $request->header('X-Frontend-URL'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('X-User-Agent'),
            'platform' => $request->header('X-Platform'),
            'screen_size' => $request->header('X-Screen-Size'),
            'status_code' => $response->status(),
            'error_message' => ($response->status() >= 400)
                ? json_decode($response->getContent(), true)['message'] ?? 'Unknown error'
                : null,
        ];

        // Dispatch event (runs asynchronously)
//        event(new ApiRequestLogged($logData));

        return $response;
    }
}
