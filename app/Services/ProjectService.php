<?php

namespace App\Services;

use App\Repositories\ProjectRepository;

class ProjectService extends BaseService
{
    public function __construct(ProjectRepository $projectRepository)
    {
        $this->repository = $projectRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'contractor_id' => 'required|integer|exists:companies,id',
            'title' => 'required|string|max:255',
            'approval_date' => 'required|date',
            'start_date' => 'required|date',
            'completion_date' => 'required|date',
            'nc_amount' => 'required',
            'total_amount' => 'required',
        ];
    }
}
