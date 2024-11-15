<?php

namespace App\Services;

use App\Repositories\ProjectContractRepository;

class ProjectContractService extends BaseService
{
    public function __construct(ProjectContractRepository $projectContractRepository)
    {
        parent::__construct($projectContractRepository);
    }

    public function rules($action = "store"): array
    {
        $rules = [
            'vendor_id' => 'required|integer|exists:vendors,id',
            'board_project_id' => 'required|integer|exists:board_projects,id',
            'department_id' => 'required|integer|exists:departments,id',
            'acceptance_letter' => 'nullable|string|max:255',
            'date_of_acceptance' => 'nullable|date',
            'total_contract_value' => 'required|numeric|min:1',
            'total_project_value' => 'required|numeric|min:1',
            'nb_vendor_change' => 'nullable|string|in:na,request,approved,rejected',
            'contract_code' => 'required|string|max:11',
            'state' => 'sometimes|nullable|string|in:na,uncompleted,completed,partial',
        ];

        if ($action === "store") {
            $rules['contract_code'] .= '|unique:project_contracts,contract_code';
        }

        return $rules;
    }
}
