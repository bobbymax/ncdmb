<?php

namespace App\Http\Controllers;


use App\Http\Resources\InventoryTransferItemResource;
use App\Services\InventoryTransferItemService;

class InventoryTransferItemController extends BaseController
{
    public function __construct(InventoryTransferItemService $inventoryTransferItemService) {
        parent::__construct($inventoryTransferItemService, 'InventoryTransferItem', InventoryTransferItemResource::class);
    }
}
