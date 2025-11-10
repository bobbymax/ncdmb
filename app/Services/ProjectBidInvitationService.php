<?php

namespace App\Services;

use App\Repositories\ProjectBidInvitationRepository;

class ProjectBidInvitationService extends BaseService
{
    public function __construct(ProjectBidInvitationRepository $projectBidInvitationRepository)
    {
        parent::__construct($projectBidInvitationRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:500',
            'description' => 'nullable|string',
            'technical_specifications' => 'nullable|string',
            'scope_of_work' => 'nullable|string',
            'deliverables' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'required_documents' => 'nullable|array',
            'eligibility_criteria' => 'nullable|array',
            'bid_security_required' => 'boolean',
            'bid_security_amount' => 'nullable|numeric|min:0',
            'bid_security_validity_days' => 'nullable|integer|min:1',
            'estimated_contract_value' => 'nullable|numeric|min:0',
            'advertisement_date' => 'nullable|date',
            'pre_bid_meeting_date' => 'nullable|date',
            'pre_bid_meeting_location' => 'nullable|string|max:500',
            'submission_deadline' => 'required|date|after:today',
            'bid_validity_days' => 'nullable|integer|min:1',
            'opening_date' => 'required|date|after:submission_deadline',
            'opening_location' => 'nullable|string|max:500',
            'evaluation_criteria' => 'nullable|array',
            'technical_weight' => 'nullable|numeric|min:0|max:100',
            'financial_weight' => 'nullable|numeric|min:0|max:100',
            'published_newspapers' => 'nullable|array',
            'published_bpp_portal' => 'boolean',
            'status' => 'nullable|in:draft,published,closed,cancelled',
        ];
    }
}
