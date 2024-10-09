<?php

namespace App\Services;

use App\Repositories\ProjectScopeRepository;

class ProjectScopeService extends BaseService
{
    public function __construct(ProjectScopeRepository $projectScopeRepository)
    {
        $this->repository = $projectScopeRepository;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'required|integer|exists:projects,id',
            'schedule_id' => 'required|integer|exists:schedules,id',
            'contractor_id' => 'required|integer|exists:companies,id',
            'scope' => 'required|string',
            'nc_scope' => 'required|string',
            'non_nc_scope' => 'required|string',
            'contract_value' => 'required',
            'nc_value' => 'required',
            'no_of_nigerians' => 'required|integer',
            'no_of_expatriates' => 'required|integer',
            'ngn_man_hrs' => 'required|integer',
            'expatriates_man_hrs' => 'required|integer',
            'expected_start_date' => 'required|date',
            'expected_completion_date' => 'required|date',
        ];
    }
}
