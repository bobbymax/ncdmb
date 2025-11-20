<?php

namespace App\Repositories;

use App\Models\LegalAuditTrail;

class LegalAuditTrailRepository extends BaseRepository
{
    public function __construct(LegalAuditTrail $legalAuditTrail) {
        parent::__construct($legalAuditTrail);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'action_type' => $data['action_type'] ?? 'other',
            'performed_at' => $data['performed_at'] ?? now(),
            'performed_by' => $data['performed_by'] ?? \Illuminate\Support\Facades\Auth::id(),
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'user_agent' => $data['user_agent'] ?? request()->userAgent(),
        ];
    }
}
