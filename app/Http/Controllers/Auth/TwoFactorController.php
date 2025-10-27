<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TwoFactorController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate 2FA secret and QR code for user
     */
    public function generate(Request $request)
    {
        $user = $request->user();
        
        // Generate secret
        $secret = $this->google2fa->generateSecretKey();
        
        // Store temporarily in session (not in DB yet)
        session(['2fa_secret' => $secret]);
        
        // Generate QR code URL
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name', 'NCDMB Portal'),
            $user->email ?? $user->staff_no ?? 'user',
            $secret
        );
        
        return response()->json([
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
        ]);
    }

    /**
     * Confirm and enable 2FA
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();
        $secret = session('2fa_secret');

        if (!$secret) {
            return response()->json([
                'message' => '2FA setup not initiated'
            ], 400);
        }

        // Verify the code
        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return response()->json([
                'message' => 'Invalid authentication code'
            ], 422);
        }

        // Enable 2FA
        $user->enableTwoFactorAuthentication($secret);
        
        // Generate recovery codes
        $recoveryCodes = $user->generateRecoveryCodes();
        
        // Clear session
        session()->forget('2fa_secret');

        return response()->json([
            'message' => '2FA enabled successfully',
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid password'
            ], 422);
        }

        $user->disableTwoFactorAuthentication();

        return response()->json([
            'message' => '2FA disabled successfully'
        ]);
    }

    /**
     * Verify 2FA code during login
     */
    public function verify(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'code' => 'required|string',
        ]);

        $user = \App\Models\User::find($request->user_id);

        if (!$user || !$user->two_factor_enabled) {
            return response()->json([
                'message' => '2FA not enabled for this user'
            ], 400);
        }

        // Check if it's a recovery code
        $recoveryCodes = $user->getRecoveryCodes();
        if (in_array(strtoupper($request->code), $recoveryCodes)) {
            // Remove used recovery code
            $remainingCodes = array_diff($recoveryCodes, [strtoupper($request->code)]);
            $user->forceFill([
                'two_factor_recovery_codes' => encrypt(json_encode(array_values($remainingCodes)))
            ])->save();
            
            // Log the user in
            Auth::login($user);
            $request->session()->regenerate();
            
            return response()->json([
                'message' => 'Login successful (recovery code used)',
                'recovery_codes_remaining' => count($remainingCodes),
            ]);
        }

        // Verify TOTP code
        $secret = $user->getTwoFactorSecret();
        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return response()->json([
                'message' => 'Invalid authentication code'
            ], 422);
        }

        // Log the user in
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login successful'
        ]);
    }

    /**
     * Get 2FA status for current user
     */
    public function status(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'enabled' => $user->two_factor_enabled ?? false,
            'confirmed_at' => $user->two_factor_confirmed_at,
            'has_recovery_codes' => !empty($user->getRecoveryCodes()),
            'recovery_codes_count' => count($user->getRecoveryCodes()),
        ]);
    }
}

