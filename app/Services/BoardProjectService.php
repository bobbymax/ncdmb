<?php

namespace App\Services;

use App\Repositories\BoardProjectRepository;

class BoardProjectService extends BaseService
{
    public function __construct(BoardProjectRepository $boardProjectRepository)
    {
        $this->repository = $boardProjectRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'contractor_id' => 'required|integer|exists:companies,id',
            'department_id' => 'required|integer|exists:departments,id',
            'title' => 'required|string|max:255',
            'approval_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'completion_date' => 'nullable|date',
            'ownership_type' => 'required|string|max:255',
            'percentage_committed' => 'required|integer',
            'total_amount' => 'required',
        ];
    }
}
