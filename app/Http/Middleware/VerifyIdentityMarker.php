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
        $parts = explode(':', $identityMarker);
        
        // Validate format
        if (count($parts) !== 3) {
            return response()->json(['error' => 'Invalid identity marker format'], 403);
        }
        
        [$userId, $timestamp, $signature] = $parts;

        // Verify if the user is authenticated and matches the marker
        if (!Auth::check() || Auth::id() != $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validate secret key is configured
        $secretKey = env('IDENTITY_SECRET_KEY', 'ncdmb-staff-user');
        if (empty($secretKey)) {
            \Log::error('IDENTITY_SECRET_KEY not configured in environment');
            return response()->json(['error' => 'Server configuration error'], 500);
        }

        // Generate the expected signature
        $expectedSignature = hash_hmac('sha256', "{$userId}:{$timestamp}", $secretKey);

        // Check if the signature matches
        if (!hash_equals($expectedSignature, $signature)) {
            \Log::warning('Identity marker signature mismatch', [
                'user_id' => $userId,
                'expected' => substr($expectedSignature, 0, 10) . '...',
                'received' => substr($signature, 0, 10) . '...',
            ]);
            return response()->json(['error' => 'Invalid identity marker'], 403);
        }

        // Check if the timestamp is within an acceptable time range
        // Allow 10 minutes tolerance (increased from 5) to account for:
        // - Clock skew between client and server
        // - Network latency
        // - Request processing time
        $serverTime = time() * 1000; // Convert to milliseconds
        $clientTime = (int) $timestamp;
        $timeDifference = abs($serverTime - $clientTime);
        $maxTolerance = 10 * 60 * 1000; // 10 minutes in milliseconds

        if ($timeDifference > $maxTolerance) {
            \Log::warning('Identity marker timestamp validation failed', [
                'server_time' => $serverTime,
                'client_time' => $clientTime,
                'difference_ms' => $timeDifference,
                'max_tolerance_ms' => $maxTolerance,
            ]);
            return response()->json(['error' => 'Request expired'], 403);
        }

        return $next($request);
    }
}
