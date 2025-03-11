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
        [$userId, $timestamp, $signature] = explode(':', $identityMarker);

        // Verify user authentication
        if (!Auth::check() || Auth::id() != $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Generate expected signature
        $expectedSignature = hash_hmac('sha256', "{$userId}:{$timestamp}", env('IDENTITY_SECRET_KEY'));

        // Verify signature
        if (!hash_equals($expectedSignature, $signature)) {
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
