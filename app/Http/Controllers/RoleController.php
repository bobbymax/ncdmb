<?php

namespace App\Http\Controllers;

use App\Services\RoleService;

class RoleController extends Controller
{
    public function __construct(RoleService $roleService) {
        $this->service = $roleService;
        $this->name = 'Role';
    }
}
