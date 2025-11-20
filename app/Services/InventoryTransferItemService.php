<?php

namespace App\Services;

use App\Repositories\InventoryTransferItemRepository;

class InventoryTransferItemService extends BaseService
{
    public function __construct(InventoryTransferItemRepository $inventoryTransferItemRepository)
    {
        parent::__construct($inventoryTransferItemRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'inventory_transfer_id' => 'required|integer|exists:inventory_transfers,id',
            'product_id' => 'required|integer|exists:products,id',
            'product_measurement_id' => 'nullable|integer|exists:product_measurements,id',
            'batch_id' => 'nullable|integer|exists:inventory_batches,id',
            'quantity_transferred' => 'required|numeric|min:0.0001',
            'quantity_received' => 'nullable|numeric|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ];
    }
}
