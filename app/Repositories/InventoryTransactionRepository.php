<?php

namespace App\Repositories;

use App\Models\InventoryTransaction;

class InventoryTransactionRepository extends BaseRepository
{
    public function __construct(InventoryTransaction $inventoryTransaction) {
        parent::__construct($inventoryTransaction);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
