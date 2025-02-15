<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedOrigins = ['http://localhost:3000', 'https://portal.test'];
        $origin = $request->headers->get('Origin');

        // Handle preflight requests (OPTIONS) before Laravel processes them
        if ($request->isMethod('OPTIONS')) {
            Log::info("🔄 Handling CORS Preflight Request", ['Origin' => $origin]);

            if (in_array($origin, $allowedOrigins)) {
                return response()->json('CORS Preflight OK', 200, [
                    'Access-Control-Allow-Origin' => $origin,
                    'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
                    'Access-Control-Allow-Headers' => 'X-Requested-With, Content-Type, X-Token-Auth, Authorization, X-XSRF-TOKEN, X-CSRF-TOKEN, Set-Cookie',
                    'Access-Control-Allow-Credentials' => 'true',
                    'Access-Control-Max-Age' => '3600',
                ]);
            }

            return response()->json('CORS Preflight Failed', 403);
        }

        $response = $next($request);

        // Apply CORS headers only when Origin is allowed
        if (in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, X-Token-Auth, Authorization, X-XSRF-TOKEN, X-CSRF-TOKEN, Set-Cookie');
        $response->headers->set('Access-Control-Max-Age', '3600');

        return $response;
    }
}
