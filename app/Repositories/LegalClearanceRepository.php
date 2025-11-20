<?php

namespace App\Repositories;

use App\Models\LegalClearance;

class LegalClearanceRepository extends BaseRepository
{
    public function __construct(LegalClearance $legalClearance) {
        parent::__construct($legalClearance);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'clearance_type' => $data['clearance_type'] ?? 'pre_signing',
            'clearance_status' => $data['clearance_status'] ?? 'pending',
            'clearance_reference' => $data['clearance_reference'] ?? $this->generate('clearance_reference', 'LEG-CLR'),
        ];
    }
}
