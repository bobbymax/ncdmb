<?php

namespace App\Services;

use App\Repositories\GroupRepository;

class GroupService extends BaseService
{
    public function __construct(GroupRepository $groupRepository)
    {
        parent::__construct($groupRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string',
        ];
    }
}
