<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function __construct(UserService $userService)
    {
        parent::__construct($userService, 'User', UserResource::class);
    }

    /**
     * Handle an incoming authentication request.
     * @throws ValidationException
     */
    public function store(LoginRequest|Request $request): \Illuminate\Http\JsonResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return $this->noContent();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
