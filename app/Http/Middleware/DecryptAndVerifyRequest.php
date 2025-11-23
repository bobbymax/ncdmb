<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DecryptAndVerifyRequest
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

        // Extract user ID and timestamp
        $parts = explode(':', $identityMarker);
        
        // Validate format
        if (count($parts) !== 3) {
            return response()->json(['error' => 'Invalid identity marker format'], 403);
        }
        
        [$userId, $timestamp, $signature] = $parts;

        // Verify user authentication
        if (!Auth::check() || Auth::id() != $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validate secret key is configured
        $secretKey = env('IDENTITY_SECRET_KEY', 'ncdmb-staff-user');
        if (empty($secretKey)) {
            \Log::error('IDENTITY_SECRET_KEY not configured in environment');
            return response()->json(['error' => 'Server configuration error'], 500);
        }

        // Generate expected signature
        $expectedSignature = hash_hmac('sha256', "{$userId}:{$timestamp}", $secretKey);

        // Verify signature
        if (!hash_equals($expectedSignature, $signature)) {
            \Log::warning('Identity marker signature mismatch in DecryptAndVerifyRequest', [
                'user_id' => $userId,
                'expected' => substr($expectedSignature, 0, 10) . '...',
                'received' => substr($signature, 0, 10) . '...',
            ]);
            return response()->json(['error' => 'Invalid identity marker'], 403);
        }

        // Decrypt request data if it's encrypted
        if ($request->header('X-Encrypted') === 'true') {
            try {
                // Decrypt the request body
                $encryptedPayload = $request->getContent();

                // Ensure the payload is in Base64 format before decryption
                $decryptedData = openssl_decrypt(
                    base64_decode($encryptedPayload), // Decode from Base64
                    'AES-256-CBC', // Same algorithm as CryptoJS
                    env('IDENTITY_SECRET_KEY'), // Key must match frontend
                    0,
                    env('ENCRYPTION_IV') // Use the same IV for encryption
                );

                if (!$decryptedData) {
                    return response()->json(['error' => 'Decryption failed'], 403);
                }

                $request->replace(json_decode($decryptedData, true));
            } catch (\Exception $e) {
                return response()->json(['error' => 'Decryption failed' . $request->getContent() ], 403);
            }
        }

        return $next($request);
    }
}
