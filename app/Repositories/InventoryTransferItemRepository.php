<?php

namespace App\Repositories;

use App\Models\InventoryTransferItem;

class InventoryTransferItemRepository extends BaseRepository
{
    public function __construct(InventoryTransferItem $inventoryTransferItem) {
        parent::__construct($inventoryTransferItem);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
