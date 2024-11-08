<?php

namespace App\Services;

use App\Repositories\EQApprovalRepository;

class EQApprovalService extends BaseService
{
    public function __construct(EQApprovalRepository $eQApprovalRepository)
    {
        $this->repository = $eQApprovalRepository;
    }

    public function rules($action = "store")
    {
        return [
            'contractor_id' => 'required|integer|exists:companies,id',
            'approval_type' => 'required|string|max:255',
            'date_applied' => 'required|date',
            'date_approved' => 'required|date',
            'moi_date_approved' => 'required|date',
        ];
    }
}
