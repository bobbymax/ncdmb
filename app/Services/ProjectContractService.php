<?php

namespace App\Services;

use App\Repositories\ProjectContractRepository;

class ProjectContractService extends BaseService
{
    public function __construct(ProjectContractRepository $projectContractRepository)
    {
        $this->repository = $projectContractRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'vendor_id' => 'required|integer|exists:vendors,id',
            'board_project_id' => 'required|integer|exists:board_projects,id',
            'department_id' => 'required|integer|exists:departments,id',
            'acceptance_letter' => 'nullable|string|max:255',
            'date_of_acceptance' => 'nullable|date',
            'total_contract_value' => 'required|numeric|min:1',
            'total_project_value' => 'required|numeric|min:1',
            'nb_vendor_change' => 'nullable|string|in:na,request,approved,rejected',
        ];
    }
}
