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
            'budget_project_activity_id' => 'required|integer|exists:budget_project_activities,id',
            'vendor_id' => 'required|integer|exists:vendors,id',
            'fund_id' => 'required|integer|exists:funds,id',
            'title' => 'required|string|max:255',
            'description' => 'sometimes|nullable|string|min:3',
            'total_proposed_value' => 'required|numeric|min:1',
            'total_revised_value' => 'sometimes|numeric|min:0',
            'total_approved_value' => 'sometimes|numeric|min:0',
            'proposed_start_date' => 'required|date',
            'proposed_completion_date' => 'required|date|after_or_equal:proposed_start_date',
            'actual_start_date' => 'sometimes|nullable|date',
            'actual_completion_date' => 'sometimes|nullable|date|after_or_equal:proposed_start_date',
            'approval_threshold' => 'required|string|in:work-order,tenders,minister,fec,other',
            'is_archived' => 'required|integer|in:0,1',
            'budget_year' => 'required|integer|digits:4'
        ];
    }
}
