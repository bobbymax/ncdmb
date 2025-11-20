<?php

namespace App\Services;

use App\Repositories\InventoryReceiptItemRepository;

class InventoryReceiptItemService extends BaseService
{
    public function __construct(InventoryReceiptItemRepository $inventoryReceiptItemRepository)
    {
        parent::__construct($inventoryReceiptItemRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'inventory_receipt_id' => 'required|integer|exists:inventory_receipts,id',
            'product_id' => 'required|integer|exists:products,id',
            'product_measurement_id' => 'nullable|integer|exists:product_measurements,id',
            'batch_id' => 'nullable|integer|exists:inventory_batches,id',
            'quantity_received' => 'required|numeric|min:0.0001',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ];
    }
}
