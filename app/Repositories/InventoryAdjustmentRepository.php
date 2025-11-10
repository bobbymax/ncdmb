<?php

namespace App\Repositories;

use App\Models\InventoryAdjustment;

class InventoryAdjustmentRepository extends BaseRepository
{
    public function __construct(InventoryAdjustment $inventoryAdjustment) {
        parent::__construct($inventoryAdjustment);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
