<?php

namespace App\Services;

use App\Repositories\InventoryTransferRepository;

class InventoryTransferService extends BaseService
{
    public function __construct(InventoryTransferRepository $inventoryTransferRepository)
    {
        parent::__construct($inventoryTransferRepository);
    }

    public function rules($action = "store"): array
    {
        $rules = [
            'from_location_id' => 'required|integer|exists:inventory_locations,id',
            'to_location_id' => 'required|integer|exists:inventory_locations,id|different:from_location_id',
            'transferred_by' => 'required|integer|exists:users,id',
            'received_by' => 'nullable|integer|exists:users,id',
            'reference' => 'required|string|max:255',
            'status' => 'nullable|string|in:pending,in-transit,completed,cancelled',
            'transferred_at' => 'required|date',
            'received_at' => 'nullable|date|after_or_equal:transferred_at',
            'remarks' => 'nullable|string',
        ];

        if ($action === "store") {
            $rules['reference'] .= '|unique:inventory_transfers,reference';
        }

        return $rules;
    }
}
