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
            // Existing rules
            'user_id' => 'required|integer|exists:users,id',
            'department_id' => 'required|integer|exists:departments,id',
            'threshold_id' => 'required|integer|exists:thresholds,id',
            'project_category_id' => 'required|integer|exists:project_categories,id',
            'title' => 'required|string|max:500',
            'description' => 'nullable|string',
            'total_proposed_amount' => 'nullable|numeric|min:0',
            'sub_total_amount' => 'nullable|numeric|min:0',
            'service_charge_percentage' => 'nullable|integer|min:0|max:100',
            'markup_amount' => 'nullable|numeric|min:0',
            'vat_amount' => 'nullable|numeric|min:0',
            'proposed_start_date' => 'nullable|date',
            'proposed_end_date' => 'nullable|date|after_or_equal:proposed_start_date',
            'type' => 'required|string|in:staff,third-party',
            'status' => 'required|string|in:pending,registered,approved,denied,kiv,discussed',

            // New rules - Classification
            'project_type' => 'required|string|in:capital,operational,maintenance,research,infrastructure',
            'priority' => 'required|string|in:critical,high,medium,low',
            'strategic_alignment' => 'nullable|string|max:1000',

            // New rules - Financial
            'fund_id' => 'nullable|integer',
            'budget_year' => 'nullable|string|max:20',

            // Existing but enhanced
            'variation_amount' => 'nullable|numeric',
            'total_approved_amount' => 'nullable|numeric|min:0',
        ];
    }
}
