<?php

namespace App\Repositories;

use App\Models\ProjectBidInvitation;

class ProjectBidInvitationRepository extends BaseRepository
{
    public function __construct(ProjectBidInvitation $projectBidInvitation) 
    {
        parent::__construct($projectBidInvitation);
    }

    public function parse(array $data): array
    {
        return [
            'project_id' => $data['project_id'],
            'invitation_reference' => $data['invitation_reference'] ?? $this->generateInvitationReference(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'technical_specifications' => $data['technical_specifications'] ?? null,
            'scope_of_work' => $data['scope_of_work'] ?? null,
            'deliverables' => $data['deliverables'] ?? null,
            'terms_and_conditions' => $data['terms_and_conditions'] ?? null,
            'required_documents' => $data['required_documents'] ?? null,
            'eligibility_criteria' => $data['eligibility_criteria'] ?? null,
            'bid_security_required' => $data['bid_security_required'] ?? true,
            'bid_security_amount' => $data['bid_security_amount'] ?? null,
            'bid_security_validity_days' => $data['bid_security_validity_days'] ?? 90,
            'estimated_contract_value' => $data['estimated_contract_value'] ?? null,
            'advertisement_date' => $data['advertisement_date'] ?? null,
            'pre_bid_meeting_date' => $data['pre_bid_meeting_date'] ?? null,
            'pre_bid_meeting_location' => $data['pre_bid_meeting_location'] ?? null,
            'submission_deadline' => $data['submission_deadline'],
            'bid_validity_days' => $data['bid_validity_days'] ?? 90,
            'opening_date' => $data['opening_date'],
            'opening_location' => $data['opening_location'] ?? null,
            'evaluation_criteria' => $data['evaluation_criteria'] ?? null,
            'technical_weight' => $data['technical_weight'] ?? 70.00,
            'financial_weight' => $data['financial_weight'] ?? 30.00,
            'published_newspapers' => $data['published_newspapers'] ?? null,
            'published_bpp_portal' => $data['published_bpp_portal'] ?? false,
            'tender_document_url' => $data['tender_document_url'] ?? null,
            'bill_of_quantities_url' => $data['bill_of_quantities_url'] ?? null,
            'status' => $data['status'] ?? 'draft',
        ];
    }

    private function generateInvitationReference(): string
    {
        $year = date('Y');
        $count = $this->model->whereYear('created_at', $year)->count() + 1;
        return sprintf('TENDER/%s/%04d', $year, $count);
    }
}
