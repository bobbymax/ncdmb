<?php

namespace App\Services;

use App\Repositories\InventoryReceiptRepository;

class InventoryReceiptService extends BaseService
{
    public function __construct(InventoryReceiptRepository $inventoryReceiptRepository)
    {
        parent::__construct($inventoryReceiptRepository);
    }

    public function rules($action = "store"): array
    {
        $rules = [
            'store_supply_id' => 'required|integer|exists:store_supplies,id',
            'received_by' => 'required|integer|exists:users,id',
            'location_id' => 'required|integer|exists:inventory_locations,id',
            'reference' => 'required|string|max:255',
            'received_at' => 'required|date',
            'status' => 'nullable|string|in:pending,partial,complete',
            'remarks' => 'nullable|string',
        ];

        if ($action === "store") {
            $rules['reference'] .= '|unique:inventory_receipts,reference';
        }

        return $rules;
    }
}
