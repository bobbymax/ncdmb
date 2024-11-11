<?php

namespace App\Http\Controllers;

use App\Services\PermissionService;

class PermissionController extends Controller
{
    public function __construct(PermissionService $permissionService) {
        $this->service = $permissionService;
        $this->name = 'Permission';
    }
}
