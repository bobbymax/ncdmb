<?php

namespace App\Services;

use App\Repositories\ActivityLogRepository;

class ActivityLogService extends BaseService
{
    public function __construct(ActivityLogRepository $activityLogRepository)
    {
        parent::__construct($activityLogRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'department_id' => 'required|integer|exists:departments,id',
            'action_category' => 'required|string|max:255',
            'description' => 'required|string|min:3',
            'logable_id' => 'nullable|integer',
            'loable_type' => 'nullable|string'
        ];
    }
}
