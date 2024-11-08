<?php

namespace App\Services;

use App\Repositories\PrincipalInvestigatorRepository;

class PrincipalInvestigatorService extends BaseService
{
    public function __construct(PrincipalInvestigatorRepository $principalInvestigatorRepository)
    {
        $this->repository = $principalInvestigatorRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'r_n_d_project_id' => 'required|integer|exists:r_n_d_projects,id',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'required|string|max:255',
            'qualification' => 'nullable|string|max:255',
        ];
    }
}
