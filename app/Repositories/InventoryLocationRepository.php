<?php

namespace App\Repositories;

use App\Models\InventoryLocation;

class InventoryLocationRepository extends BaseRepository
{
    public function __construct(InventoryLocation $inventoryLocation) {
        parent::__construct($inventoryLocation);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
