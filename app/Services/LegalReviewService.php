<?php

namespace App\Services;

use App\Repositories\LegalReviewRepository;

class LegalReviewService extends BaseService
{
    public function __construct(LegalReviewRepository $legalReviewRepository)
    {
        parent::__construct($legalReviewRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'project_contract_id' => 'nullable|integer|exists:project_contracts,id',
            'project_id' => 'nullable|integer|exists:projects,id',
            'document_id' => 'nullable|integer|exists:documents,id',
            'review_type' => 'required|string|in:contract_review,compliance_check,risk_assessment,variation_review,termination_review,other',
            'reviewed_by' => 'required|integer|exists:users,id',
            'review_status' => 'nullable|string|in:pending,in_review,approved,rejected,conditional',
            'review_date' => 'nullable|date',
            'legal_opinion' => 'nullable|string',
            'compliance_score' => 'nullable|numeric|min:0|max:100',
            'risks_identified' => 'nullable|array',
            'recommendations' => 'nullable|string',
            'requires_revision' => 'nullable|boolean',
            'revision_notes' => 'nullable|string',
            'approved_by' => 'nullable|integer|exists:users,id',
            'approval_date' => 'nullable|date',
            'rejection_reason' => 'nullable|string',
        ];
    }
}
