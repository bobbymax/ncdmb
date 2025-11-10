<?php

namespace App\Repositories;

use App\Models\ProjectBidEvaluation;

class ProjectBidEvaluationRepository extends BaseRepository
{
    public function __construct(ProjectBidEvaluation $projectBidEvaluation) 
    {
        parent::__construct($projectBidEvaluation);
    }

    public function parse(array $data): array
    {
        return [
            'project_bid_id' => $data['project_bid_id'],
            'evaluator_id' => $data['evaluator_id'] ?? auth()->id(),
            'evaluation_type' => $data['evaluation_type'],
            'evaluation_date' => $data['evaluation_date'] ?? now()->toDateString(),
            'criteria' => $data['criteria'] ?? null,
            'total_score' => $data['total_score'] ?? null,
            'pass_fail' => $data['pass_fail'] ?? null,
            'comments' => $data['comments'] ?? null,
            'recommendations' => $data['recommendations'] ?? null,
            'status' => $data['status'] ?? 'draft',
        ];
    }
}
