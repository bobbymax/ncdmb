<?php

namespace App\Http\Controllers;

use App\Services\UserService;

class UserController extends Controller
{
    public function __construct(UserService $userService) {
        $this->service = $userService;
        $this->name = 'User';
    }
}
