<?php

namespace App\Services;

use App\Http\Resources\PermissionResource;
use App\Repositories\PermissionRepository;

class PermissionService extends BaseService
{
    public function __construct(
        PermissionRepository $permissionRepository,
        PermissionResource $permissionResource
    ) {
        parent::__construct($permissionRepository, $permissionResource);
    }

    public function rules($action = "store"): array
    {
        return [
            'page_id' => 'required|integer|exists:pages,id',
            'name' => 'required|string|max:255',
        ];
    }
}
