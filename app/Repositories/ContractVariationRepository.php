<?php

namespace App\Repositories;

use App\Models\ContractVariation;

class ContractVariationRepository extends BaseRepository
{
    public function __construct(ContractVariation $contractVariation) {
        parent::__construct($contractVariation);
    }

    public function parse(array $data): array
    {
        // Auto-calculate new_total_value if not provided
        $newTotalValue = $data['new_total_value'] ?? null;
        if (!$newTotalValue && isset($data['original_value']) && isset($data['variation_amount'])) {
            $newTotalValue = $data['original_value'] + $data['variation_amount'];
        }

        return [
            ...$data,
            'variation_type' => $data['variation_type'] ?? 'price_adjustment',
            'variation_reference' => $data['variation_reference'] ?? $this->generate('variation_reference', 'VAR'),
            'new_total_value' => $newTotalValue,
            'initiated_date' => $data['initiated_date'] ?? now()->toDateString(),
            'initiated_by' => $data['initiated_by'] ?? \Illuminate\Support\Facades\Auth::id(),
            'approval_status' => $data['approval_status'] ?? 'pending',
        ];
    }
}
