<?php

namespace App\Repositories;

use App\Models\InventoryReturn;

class InventoryReturnRepository extends BaseRepository
{
    public function __construct(InventoryReturn $inventoryReturn) {
        parent::__construct($inventoryReturn);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
