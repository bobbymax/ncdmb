<?php

namespace App\Services;

use App\Repositories\InventoryBalanceRepository;

class InventoryBalanceService extends BaseService
{
    public function __construct(InventoryBalanceRepository $inventoryBalanceRepository)
    {
        parent::__construct($inventoryBalanceRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'product_measurement_id' => 'nullable|exists:product_measurements,id',
            'location_id' => 'required|exists:inventory_locations,id',
            'on_hand' => 'nullable|numeric',
            'reserved' => 'nullable|numeric',
            'available' => 'nullable|numeric',
            'unit_cost' => 'nullable|numeric|min:0',
            'last_movement_at' => 'nullable|date',
        ];
    }
}
