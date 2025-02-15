<?php

namespace App\Services;


use App\Repositories\RoleRepository;

class RoleService extends BaseService
{
    public function __construct(RoleRepository $roleRepository)
    {
        parent::__construct($roleRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'department_id' => 'required|integer|exists:departments,id',
            'access_level' => 'required|string|in:basic,operative,control,command,sovereign,system',
            'name' => 'required|string|max:255',
            'slots' => 'required|integer|min:1',
            'issued_date' => 'nullable|date',
            'expired_date' => 'nullable|date',
        ];
    }
}
