<?php

namespace App\Services;

use App\Repositories\ProjectBidEvaluationRepository;

class ProjectBidEvaluationService extends BaseService
{
    public function __construct(ProjectBidEvaluationRepository $projectBidEvaluationRepository)
    {
        parent::__construct($projectBidEvaluationRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'project_bid_id' => 'required|exists:project_bids,id',
            'evaluator_id' => 'nullable|exists:users,id',
            'evaluation_type' => 'required|in:administrative,technical,financial,post_qualification',
            'evaluation_date' => 'nullable|date',
            'criteria' => 'nullable|array',
            'total_score' => 'nullable|numeric|min:0|max:100',
            'pass_fail' => 'nullable|in:pass,fail,conditional',
            'comments' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'status' => 'nullable|in:draft,submitted,reviewed,approved',
        ];
    }
}
