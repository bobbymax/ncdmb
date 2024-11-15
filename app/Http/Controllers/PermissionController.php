<?php

namespace App\Http\Controllers;

use App\Http\Resources\PermissionResource;
use App\Services\PermissionService;

class PermissionController extends Controller
{
    public function __construct(PermissionService $permissionService) {
        parent::__construct($permissionService, 'Permission', PermissionResource::class);
    }
}
