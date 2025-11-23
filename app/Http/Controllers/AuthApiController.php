<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuthUserResource;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Authentication and authorization endpoints"
 * )
 */
class AuthApiController extends BaseController
{
    use ApiResponse;

    public function __construct(UserService $userService)
    {
        parent::__construct($userService, 'Authentication', AuthUserResource::class);
    }

    public function getChatToken(Request $request): \Illuminate\Http\JsonResponse
    {
        // Check if user is authenticated via session
        if (!Auth::check()) {
            return $this->error(null, 'User not authenticated', 401);
        }

        $user = Auth::user();

        // Generate a chat-specific token
        $chatToken = $user->createToken('chat-token', ['chat:read', 'chat:write'])->plainTextToken;

        return response()->json([
            'token' => $chatToken,
            'expires_in' => 3600, // 1 hour
            'user' => new $this->jsonResource($user)
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/refresh",
     *     summary="Refresh authentication token",
     *     description="Refresh the access token using a valid refresh token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"refresh_token"},
     *             @OA\Property(property="refresh_token", type="string", example="refresh_token_here")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="new_access_token"),
     *             @OA\Property(property="refresh_token", type="string", example="new_refresh_token"),
     *             @OA\Property(property="staff", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid refresh token",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function refreshToken(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate the refresh token
        $refreshToken = $request->refresh_token;

        if (!$refreshToken) {
            return $this->error(null,'Refresh token is missing', 400);
        }

        // Decode the refresh token and validate it
        $user = $this->service->getRecordByColumn('refresh_token', $refreshToken);

        if (!$user) {
            return $this->error(null,'Refresh token is invalid', 401);
        }

        // Optionally, check if the refresh token has expired
        // For simplicity, assuming it's still valid

        // Generate a new access token
        $newAccessToken = $user->createToken('authToken')->plainTextToken;

        // Optionally: Generate a new refresh token
        $newRefreshToken = Hash::make(uniqid()); // Simple example, customize as needed
        $user->update(['refresh_token' => $newRefreshToken]);

        return response()->json([
            'token' => $newAccessToken,
            'refresh_token' => $newRefreshToken, // If you're issuing a new one
            'staff' => new $this->jsonResource($user)
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="User login",
     *     description="Authenticate a user and create a session. If 2FA is enabled, returns a 2FA requirement response.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password"},
     *             @OA\Property(property="username", type="string", example="user@example.com", description="User email or staff number"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful or 2FA required",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     type="object",
     *                     @OA\Property(property="success", type="boolean", example=true),
     *                     @OA\Property(property="data", type="object",
     *                         @OA\Property(property="user_id", type="integer", example=1),
     *                         @OA\Property(property="message", type="string", example="Logged in successfully")
     *                     ),
     *                     @OA\Property(property="message", type="string", example="Logged in successfully")
     *                 ),
     *                 @OA\Schema(
     *                     type="object",
     *                     @OA\Property(property="requires_2fa", type="boolean", example=true),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="message", type="string", example="Please enter your authentication code")
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $oldSession = Session::getId();

        Log::info('Old Session details ' . $oldSession);

        $validation = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'password' => 'required'
        ]);

        if ($validation->fails()) {
            return $this->error($validation->errors(), 'Please fix the following errors: ', 401);
        }

        $username = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'staff_no';

        // if validation passed gather login credentials
        $credentials = [
            $username => $request->username,
            'password' => $request->password
        ];

        if (!Auth::attempt($credentials)) {
            return $this->error(["username" => $credentials[$username], "password" => $credentials['password']], 'Invalid Credentials', 401);
        }

        $user = Auth::user();

        // Check if 2FA is enabled (with null safety)
        if ($user && isset($user->two_factor_enabled) && $user->two_factor_enabled === true) {
            // Don't complete the login - logout immediately
            Auth::logout();
            
            // Return special response indicating 2FA is required
            return response()->json([
                'requires_2fa' => true,
                'user_id' => $user->id,
                'message' => 'Please enter your authentication code'
            ], 200);
        }

        // Normal login flow (no 2FA)
        $request->session()->regenerate();
        Session::setId($oldSession);

        return $this->success([
            'user_id' => $user->id,
            'message' => 'Logged in successfully'
        ], 'Logged in successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="User logout",
     *     description="Logout the authenticated user and invalidate the session",
     *     tags={"Authentication"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", example=null),
     *             @OA\Property(property="message", type="string", example="You have successfully been logged out")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        $userId = Auth::id();
        $sessionId = Session::getId();
        
        Log::info('Logout initiated', [
            'user_id' => $userId,
            'session_id_before' => $sessionId,
        ]);
        
        // Clear web guard authentication (Sanctum uses RequestGuard which doesn't have logout)
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }
        
        // Invalidate the session completely
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        Log::info('Logout completed', [
            'user_id' => $userId,
            'session_id_after' => Session::getId(),
            'web_auth_check' => Auth::guard('web')->check(),
        ]);
        
        // Clear all authentication cookies with exact domain/path match
        $domain = config('session.domain');
        $path = config('session.path', '/');
        
        Cookie::queue(Cookie::forget('staff_portal_session', $path, $domain));
        Cookie::queue(Cookie::forget('XSRF-TOKEN', $path, $domain));
        Cookie::queue(Cookie::forget('laravel_session', $path, $domain));

        return $this->success([
            'data' => null,
            'message' => 'You have successfully been logged out',
        ]);
    }
}
