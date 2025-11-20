<?php

namespace App\Repositories;

use App\Models\InventoryReceiptItem;

class InventoryReceiptItemRepository extends BaseRepository
{
    public function __construct(InventoryReceiptItem $inventoryReceiptItem) {
        parent::__construct($inventoryReceiptItem);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
