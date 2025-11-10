<?php

namespace App\Services;

use App\Repositories\InventoryLocationRepository;

class InventoryLocationService extends BaseService
{
    public function __construct(InventoryLocationRepository $inventoryLocationRepository)
    {
        parent::__construct($inventoryLocationRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            //
        ];
    }
}
