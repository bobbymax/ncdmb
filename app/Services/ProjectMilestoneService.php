<?php

namespace App\Services;


use App\Repositories\ProjectMilestoneRepository;

class ProjectMilestoneService extends BaseService
{
    public function __construct(
        ProjectMilestoneRepository $projectMilestoneRepository
    ) {
        parent::__construct($projectMilestoneRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'board_project_id' => 'required|integer|exists:board_projects,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string|min:5',
            'total_milestone_payable_amount' => 'required|numeric|min:1',
            'percentage_project_completion' => 'required|integer|min:0|max:100',
            'expected_completion_date' => 'required|date',
            'actual_completion_date' => 'nullable|date',
            'stage' => 'required|string|in:work-order,jcc,payment-mandate,raise-payment',
            'status' => 'sometimes|string|in:pending,in-progress,completed,overdue,cancelled',
            'is_paid' => 'sometimes|boolean',
        ];
    }
}
