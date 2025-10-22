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

        $request->session()->regenerate();
        Session::setId($oldSession);

        return $this->success(null, 'Logged in successfully');
    }

    public function logout(): \Illuminate\Http\JsonResponse
    {
        Session::flush();
        Session::regenerate();
        Cookie::queue(Cookie::forget('staff_portal_session'));

        return $this->success([
            'data' => null,
            'message' => 'You have successfully been logged out',
        ]);
    }
}
