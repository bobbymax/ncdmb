<?php

namespace App\Repositories;

use App\Models\ContractDispute;

class ContractDisputeRepository extends BaseRepository
{
    public function __construct(ContractDispute $contractDispute) {
        parent::__construct($contractDispute);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'dispute_type' => $data['dispute_type'] ?? 'payment',
            'dispute_reference' => $data['dispute_reference'] ?? $this->generate('dispute_reference', 'DSP'),
            'raised_by' => $data['raised_by'] ?? 'contractor',
            'raised_date' => $data['raised_date'] ?? now()->toDateString(),
            'status' => $data['status'] ?? 'open',
        ];
    }
}
