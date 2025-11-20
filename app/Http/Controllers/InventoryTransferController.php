<?php

namespace App\Http\Controllers;


use App\Http\Resources\InventoryTransferResource;
use App\Services\InventoryTransferService;

class InventoryTransferController extends BaseController
{
    public function __construct(InventoryTransferService $inventoryTransferService) {
        parent::__construct($inventoryTransferService, 'InventoryTransfer', InventoryTransferResource::class);
    }
}
