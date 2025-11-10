<?php

namespace App\Repositories;

use App\Models\ProcurementAuditTrail;

class ProcurementAuditTrailRepository extends BaseRepository
{
    public function __construct(ProcurementAuditTrail $procurementAuditTrail) 
    {
        parent::__construct($procurementAuditTrail);
    }

    public function parse(array $data): array
    {
        return [
            'project_id' => $data['project_id'],
            'user_id' => $data['user_id'] ?? auth()->id(),
            'action' => $data['action'],
            'entity_type' => $data['entity_type'] ?? null,
            'entity_id' => $data['entity_id'] ?? null,
            'before_value' => $data['before_value'] ?? null,
            'after_value' => $data['after_value'] ?? null,
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'user_agent' => $data['user_agent'] ?? request()->userAgent(),
            'notes' => $data['notes'] ?? null,
        ];
    }
}
