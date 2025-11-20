<?php

namespace App\Repositories;

use App\Models\LegalComplianceCheck;

class LegalComplianceCheckRepository extends BaseRepository
{
    public function __construct(LegalComplianceCheck $legalComplianceCheck) {
        parent::__construct($legalComplianceCheck);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'compliance_type' => $data['compliance_type'] ?? 'procurement_act',
            'check_status' => $data['check_status'] ?? 'pending',
            'check_date' => $data['check_date'] ?? now()->toDateString(),
            'checked_by' => $data['checked_by'] ?? \Illuminate\Support\Facades\Auth::id(),
            'requires_remediation' => $data['requires_remediation'] ?? false,
        ];
    }
}
