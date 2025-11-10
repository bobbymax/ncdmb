<?php

namespace App\Services;

use App\Repositories\ProjectEvaluationCommitteeRepository;

class ProjectEvaluationCommitteeService extends BaseService
{
    public function __construct(ProjectEvaluationCommitteeRepository $projectEvaluationCommitteeRepository)
    {
        parent::__construct($projectEvaluationCommitteeRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'committee_name' => 'required|string|max:255',
            'committee_type' => 'required|in:tender_board,technical,financial,opening',
            'chairman_id' => 'required|exists:users,id',
            'members' => 'nullable|array',
            'members.*.user_id' => 'required|exists:users,id',
            'members.*.role' => 'required|in:chairman,secretary,member,observer',
            'status' => 'nullable|in:active,dissolved',
            'formed_at' => 'nullable|date',
            'dissolved_at' => 'nullable|date|after:formed_at',
        ];
    }
}
