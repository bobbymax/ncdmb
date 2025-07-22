<?php

namespace App\Services;

use App\Repositories\ProjectRepository;

class ProjectService extends BaseService
{
    public function __construct(ProjectRepository $projectRepository)
    {
        parent::__construct($projectRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'department_id' => 'required|integer|exists:departments,id',
            'threshold_id' => 'required|integer|exists:thresholds,id',
            'project_category_id' => 'required|integer|exists:project_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_proposed_amount' => 'nullable|numeric|min:0',
            'sub_total_amount' => 'nullable|numeric|min:0',
            'service_charge_percentage' => 'nullable|integer|min:0',
            'markup_amount' => 'nullable|numeric|min:0',
            'vat_amount' => 'nullable|numeric|min:0',
            'proposed_start_date' => 'nullable|date',
            'proposed_end_date' => 'nullable|date',
            'type' => 'required|string|in:staff,third-party',
            'status' => 'required|string|in:pending,registered,approved,denied,kiv,discussed'
        ];
    }
}
