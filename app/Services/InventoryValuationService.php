<?php

namespace App\Services;

use App\Repositories\InventoryValuationRepository;

class InventoryValuationService extends BaseService
{
    public function __construct(InventoryValuationRepository $inventoryValuationRepository)
    {
        parent::__construct($inventoryValuationRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'product_id' => 'required|integer|exists:products,id',
            'product_measurement_id' => 'nullable|integer|exists:product_measurements,id',
            'location_id' => 'required|integer|exists:inventory_locations,id',
            'valuation_method' => 'required|string|in:fifo,lifo,weighted_average,specific_identification',
            'unit_cost' => 'required|numeric|min:0',
            'quantity_on_hand' => 'required|numeric|min:0',
            'total_value' => 'required|numeric|min:0',
            'valued_at' => 'required|date',
            'valued_by' => 'required|integer|exists:users,id',
            'notes' => 'nullable|string',
        ];
    }
}
