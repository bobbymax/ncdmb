<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoleResource;
use App\Services\RoleService;

class RoleController extends Controller
{
    public function __construct(RoleService $roleService) {
        parent::__construct($roleService, 'Role', RoleResource::class);
    }
}
