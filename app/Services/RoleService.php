<?php

namespace App\Services;

use App\Http\Resources\RoleResource;
use App\Repositories\RoleRepository;

class RoleService extends BaseService
{
    public function __construct(RoleRepository $roleRepository, RoleResource $roleResource)
    {
        parent::__construct($roleRepository, $roleResource);
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
            'slots' => 'required|integer|min:1',
            'issued_date' => 'required|date',
            'expired_date' => 'required|date',
        ];
    }
}
