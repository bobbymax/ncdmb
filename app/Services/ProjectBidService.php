<?php

namespace App\Services;

use App\Repositories\ProjectBidRepository;

class ProjectBidService extends BaseService
{
    public function __construct(ProjectBidRepository $projectBidRepository)
    {
        parent::__construct($projectBidRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'bid_invitation_id' => 'required|exists:project_bid_invitations,id',
            'vendor_id' => 'required|exists:vendors,id',
            'bid_amount' => 'required|numeric|min:0',
            'bid_currency' => 'nullable|string|max:10',
            'submitted_at' => 'nullable|date',
            'submission_method' => 'nullable|in:physical,electronic,hybrid',
            'received_by' => 'nullable|exists:users,id',
            'bid_security_submitted' => 'boolean',
            'bid_security_type' => 'nullable|in:bank_guarantee,insurance_bond,cash',
            'bid_security_reference' => 'nullable|string|max:100',
            'bid_documents' => 'nullable|array',
            'status' => 'nullable|in:submitted,opened,responsive,non_responsive,under_evaluation,evaluated,disqualified,recommended,awarded,not_awarded',
        ];
    }
}
