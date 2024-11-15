<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuthUserResource;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthApiController extends Controller
{
    use ApiResponse;

    public function __construct(UserService $userService)
    {
        parent::__construct($userService, 'Authentication', AuthUserResource::class);
    }

    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
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

        Auth::user()->tokens()->delete();

        $token = Auth::user()->createToken('authToken')->plainTextToken;

        return $this->success(['token' => $token, 'staff' => new $this->jsonResource(Auth::user())], 'You have logged in successfully!!');
    }

    public function logout(): \Illuminate\Http\JsonResponse
    {
        Auth::user()->tokens()->delete();

        return $this->success([
            'data' => null,
            'message' => 'You have successfully been logged out',
        ]);
    }
}
