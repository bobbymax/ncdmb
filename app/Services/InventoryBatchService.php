<?php

namespace App\Services;

use App\Repositories\InventoryBatchRepository;

class InventoryBatchService extends BaseService
{
    public function __construct(InventoryBatchRepository $inventoryBatchRepository)
    {
        parent::__construct($inventoryBatchRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'store_supply_id' => 'required|exists:store_supplies,id',
            'batch_no' => 'nullable|string|max:255',
            'manufactured_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:manufactured_at',
            'meta' => 'nullable|array',
        ];
    }
}
