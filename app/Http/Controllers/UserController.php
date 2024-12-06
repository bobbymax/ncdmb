<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Services\UserService;

class UserController extends BaseController
{
    public function __construct(UserService $userService) {
        parent::__construct($userService, 'User', UserResource::class);
    }
}
