<?php

namespace App\Repositories;

use App\Models\InventoryBalance;

class InventoryBalanceRepository extends BaseRepository
{
    public function __construct(InventoryBalance $inventoryBalance) {
        parent::__construct($inventoryBalance);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
