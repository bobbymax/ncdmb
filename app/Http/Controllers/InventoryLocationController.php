<?php

namespace App\Http\Controllers;


use App\Http\Resources\InventoryLocationResource;
use App\Services\InventoryLocationService;

class InventoryLocationController extends BaseController
{
    public function __construct(InventoryLocationService $inventoryLocationService) {
        parent::__construct($inventoryLocationService, 'InventoryLocation', InventoryLocationResource::class);
    }
}
