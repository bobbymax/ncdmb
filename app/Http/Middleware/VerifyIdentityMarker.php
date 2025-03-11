<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyIdentityMarker
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the identity marker from headers
        $identityMarker = $request->header('X-Identity-Marker');

        if (!$identityMarker) {
            return response()->json(['error' => 'Identity marker missing'], 403);
        }

        // Extract user ID and timestamp from the identity marker
        [$userId, $timestamp, $signature] = explode(':', $identityMarker);

        // Verify if the user is authenticated and matches the marker
        if (!Auth::check() || Auth::id() != $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Generate the expected signature
        $expectedSignature = hash_hmac('sha256', "{$userId}:{$timestamp}", env('IDENTITY_SECRET_KEY'));

        // Check if the signature matches
        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json(['error' => 'Invalid identity marker'], 403);
        }

        // Check if the timestamp is within an acceptable time range (5 minutes)
        if (abs(time() * 1000 - (int) $timestamp) > 5 * 60 * 1000) {
            return response()->json(['error' => 'Request expired'], 403);
        }

        return $next($request);
    }
}
