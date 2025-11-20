<?php

namespace App\Services;

use App\Repositories\LegalClearanceRepository;

class LegalClearanceService extends BaseService
{
    public function __construct(LegalClearanceRepository $legalClearanceRepository)
    {
        parent::__construct($legalClearanceRepository);
    }

    public function rules($action = "store"): array
    {
        $rules = [
            'project_contract_id' => 'required|integer|exists:project_contracts,id',
            'clearance_type' => 'required|string|in:pre_award,pre_signing,variation,termination,other',
            'clearance_status' => 'nullable|string|in:pending,cleared,rejected,conditional,expired',
            'cleared_by' => 'nullable|integer|exists:users,id',
            'clearance_date' => 'nullable|date',
            'clearance_reference' => 'nullable|string|max:100',
            'conditions' => 'nullable|array',
            'expiry_date' => 'nullable|date|after:today',
            'compliance_requirements' => 'nullable|array',
            'notes' => 'nullable|string',
            'rejection_reason' => 'nullable|string',
        ];

        if ($action === "store") {
            $rules['clearance_reference'] .= '|unique:legal_clearances,clearance_reference';
        }

        return $rules;
    }
}
