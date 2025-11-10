<?php

namespace App\Repositories;

use App\Models\ProjectEvaluationCommittee;

class ProjectEvaluationCommitteeRepository extends BaseRepository
{
    public function __construct(ProjectEvaluationCommittee $projectEvaluationCommittee) 
    {
        parent::__construct($projectEvaluationCommittee);
    }

    public function parse(array $data): array
    {
        return [
            'project_id' => $data['project_id'],
            'committee_name' => $data['committee_name'],
            'committee_type' => $data['committee_type'],
            'chairman_id' => $data['chairman_id'],
            'members' => $data['members'] ?? null,
            'status' => $data['status'] ?? 'active',
            'formed_at' => $data['formed_at'] ?? now(),
            'dissolved_at' => $data['dissolved_at'] ?? null,
        ];
    }
}
