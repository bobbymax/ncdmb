<?php

namespace App\Services;

use App\Repositories\LegalAuditTrailRepository;

class LegalAuditTrailService extends BaseService
{
    public function __construct(LegalAuditTrailRepository $legalAuditTrailRepository)
    {
        parent::__construct($legalAuditTrailRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'project_contract_id' => 'nullable|integer|exists:project_contracts,id',
            'project_id' => 'nullable|integer|exists:projects,id',
            'action_type' => 'required|string|in:review_created,review_updated,review_approved,review_rejected,clearance_granted,clearance_rejected,variation_created,variation_approved,variation_rejected,compliance_check_performed,dispute_raised,dispute_resolved,document_uploaded,document_signed,other',
            'performed_by' => 'required|integer|exists:users,id',
            'performed_at' => 'required|date',
            'before_values' => 'nullable|array',
            'after_values' => 'nullable|array',
            'ip_address' => 'nullable|string|max:45',
            'user_agent' => 'nullable|string',
            'notes' => 'nullable|string',
        ];
    }
}
