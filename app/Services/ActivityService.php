<?php

namespace App\Services;

use App\Repositories\ActivityRepository;

class ActivityService extends BaseService
{
    public function __construct(ActivityRepository $activityRepository)
    {
        $this->repository = $activityRepository;
    }

    public function rules(): array
    {
        return [
            'project_scope_id' => 'required|integer|exists:project_scopes,id',
            'year' => 'required|integer',
            'month' => 'required|string|max:255',
            'total_value_spent' => 'required|numeric',
            'nc_value_spent' => 'required|numeric',
            'no_of_nigerians' => 'required|integer',
            'no_of_expatriates' => 'required|integer',
            'ngn_man_hrs' => 'required|integer',
            'expatriates_man_hrs' => 'required|integer',
        ];
    }
}
