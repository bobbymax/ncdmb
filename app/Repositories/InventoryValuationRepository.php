<?php

namespace App\Repositories;

use App\Models\InventoryValuation;

class InventoryValuationRepository extends BaseRepository
{
    public function __construct(InventoryValuation $inventoryValuation) {
        parent::__construct($inventoryValuation);
    }

    public function parse(array $data): array
    {
        // Auto-calculate total_value if not provided
        $totalValue = $data['total_value'] ?? null;
        if (!$totalValue && isset($data['unit_cost']) && isset($data['quantity_on_hand'])) {
            $totalValue = $data['unit_cost'] * $data['quantity_on_hand'];
        }

        return [
            ...$data,
            'valuation_method' => $data['valuation_method'] ?? 'weighted_average',
            'total_value' => $totalValue,
            'valued_at' => $data['valued_at'] ?? now(),
            'valued_by' => $data['valued_by'] ?? \Illuminate\Support\Facades\Auth::id(),
        ];
    }
}
