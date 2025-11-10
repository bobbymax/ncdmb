<?php

namespace App\Services;

use App\Repositories\ProcurementAuditTrailRepository;

class ProcurementAuditTrailService extends BaseService
{
    public function __construct(ProcurementAuditTrailRepository $procurementAuditTrailRepository)
    {
        parent::__construct($procurementAuditTrailRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'user_id' => 'nullable|exists:users,id',
            'action' => 'required|string|max:100',
            'entity_type' => 'nullable|string|max:100',
            'entity_id' => 'nullable|integer',
            'before_value' => 'nullable|array',
            'after_value' => 'nullable|array',
            'ip_address' => 'nullable|ip',
            'user_agent' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
        ];
    }
}
