<?php

namespace App\Services;

use App\Repositories\LegalComplianceCheckRepository;

class LegalComplianceCheckService extends BaseService
{
    public function __construct(LegalComplianceCheckRepository $legalComplianceCheckRepository)
    {
        parent::__construct($legalComplianceCheckRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'project_contract_id' => 'required|integer|exists:project_contracts,id',
            'compliance_type' => 'required|string|in:procurement_act,fiscal_responsibility,public_accounts,company_law,tax_compliance,other',
            'check_status' => 'nullable|string|in:pending,passed,failed,conditional',
            'checked_by' => 'required|integer|exists:users,id',
            'check_date' => 'required|date',
            'findings' => 'nullable|string',
            'corrective_actions' => 'nullable|string',
            'follow_up_date' => 'nullable|date|after:today',
            'compliance_score' => 'nullable|numeric|min:0|max:100',
            'requires_remediation' => 'nullable|boolean',
            'remediation_plan' => 'nullable|string',
        ];
    }
}
