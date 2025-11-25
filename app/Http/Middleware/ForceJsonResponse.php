<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Clean any output that might have been generated before this middleware
        // This prevents deprecation warnings or other output from corrupting JSON responses
        if (ob_get_level() > 0) {
            ob_clean();
        }

        // Only force JSON response for API routes
        if ($request->is('api/*')) {
            $request->headers->set('Accept', 'application/json');
        }

        $response = $next($request);

        // Ensure output buffer is clean before returning JSON response
        if ($request->is('api/*') && ob_get_level() > 0) {
            ob_clean();
        }

        return $response;
    }
}
